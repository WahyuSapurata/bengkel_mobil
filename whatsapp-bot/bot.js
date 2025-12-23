const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const axios = require('axios');

// Simpan user yang sudah disapa
let greetedUsers = new Set();

// Simpan outlet yang dipilih per user
let userOutletMap = new Map();

const client = new Client({
    authStrategy: new LocalAuth({
        clientId: "MotoCore-bot"
    }),
    puppeteer: {
        headless: 'new',
        executablePath: '/usr/bin/google-chrome',
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
            '--no-zygote',
            '--single-process'
        ]
    }
});

client.on('qr', qr => {
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    console.log('✅ WhatsApp Bot MMMAEROAUTOMOTIVE siap tanpa scan ulang!');
});

const userState = {};
const userKategoriMap = new Map();
const userJasaMap = new Map();

/* ================= HELPER MENU ================= */

const showMainMenu = async (msg, user) => {
    userState[user] = "MENU";
    userKategoriMap.delete(user);
    userJasaMap.delete(user);

    await msg.reply(
        `🏠 *Menu Utama MMMAEROAUTOMOTIVE*

1️⃣ Cek Produk
2️⃣ Paket Service
3️⃣ Riwayat Service
4️⃣ Hubungi Admin
5️⃣ Ulasan Layanan

Ketik angka *1–5*`
    );
};

const showKategoriMenu = async (msg, user, kategori) => {
    userState[user] = "KATEGORI";

    let reply = "🏢 *Daftar Kategori Produk:*\n\n";
    kategori.forEach((item, index) => {
        reply += `${index + 1}. ${item.nama_kategori}\n`;
    });

    reply += "\n0️⃣ Kembali ke Menu Utama";
    reply += "\n✍️ Ketik *menu* kapan saja untuk ke menu utama";

    await msg.reply(reply);
};

/* ================= MAIN HANDLER ================= */

client.on('message', async msg => {
    const text = msg.body.trim().toLowerCase();
    const user = msg.from;

    try {

        /* ===== RESET / MENU MANUAL ===== */
        if (["menu", "halo", "hai", "hi", "mulai"].includes(text)) {
            return showMainMenu(msg, user);
        }

        /* ================= MENU UTAMA ================= */
        if (userState[user] === "MENU") {

            // 1️⃣ PRODUK
            if (text === "1") {
                const res = await axios.get(
                    'https://bengkel.adsmotor.id/api/boot/kategori'
                );

                const kategori = res.data.data;
                if (!kategori || kategori.length === 0) {
                    await msg.reply("❌ Kategori belum tersedia.");
                    return;
                }

                userKategoriMap.set(user, kategori);
                return showKategoriMenu(msg, user, kategori);
            }

            // 2️⃣ JASA
            if (text === "2") {
                userState[user] = "JASA";

                const res = await axios.get(
                    'https://bengkel.adsmotor.id/api/boot/jasa'
                );

                const jasa = res.data;
                if (!jasa || jasa.length === 0) {
                    await msg.reply("❌ Jasa belum tersedia.");
                    return;
                }

                let reply = "🔧 *Daftar Jasa Service*\n\n";
                jasa.forEach((item, i) => {
                    reply += `${i + 1}. ${item.nama}\n`;
                    reply += `   💰 Rp ${Number(item.harga).toLocaleString('id-ID')}\n\n`;
                });

                reply += "9️⃣ Menu Utama";
                await msg.reply(reply);
                return;
            }

            // 3️⃣ RIWAYAT
            if (text === "3") {
                userState[user] = "RIWAYAT_PLAT";
                await msg.reply("📄 Masukkan *Plat Nomor Motor*\n\nContoh:\DD 1234 XYZ");
                return;
            }

            // 4️⃣ ADMIN
            if (text === "4") {
                await msg.reply(
                    `📞 *Admin MMMAEROAUTOMOTIVE*
0812-3456-7890

✍️ Ketik *menu* untuk Menu Utama`
                );
                return;
            }

            // 5️⃣ ULASAN
            if (text === "5") {
                userState[user] = "ULASAN";
                await msg.reply(
                    `⭐ *Ulasan Layanan*

5️⃣ Sangat Puas
4️⃣ Puas
3️⃣ Cukup
2️⃣ Kurang
1️⃣ Buruk`
                );
                return;
            }
        }

        /* ================= KATEGORI ================= */
        if (userState[user] === "KATEGORI" && !isNaN(text)) {

            // 0 = kembali ke menu utama
            if (text === "0") {
                return showMainMenu(msg, user);
            }

            const kategoriList = userKategoriMap.get(user);
            const idx = parseInt(text) - 1;

            if (!kategoriList || !kategoriList[idx]) {
                await msg.reply("❌ Nomor kategori tidak valid.");
                return;
            }

            const kategori = kategoriList[idx];
            const res = await axios.get(
                `https://bengkel.adsmotor.id/api/boot/produk/${kategori.uuid}`
            );

            const produk = res.data.data;

            if (!produk || produk.length === 0) {
                await msg.reply("📦 Produk kosong, silakan pilih kategori lain.");
                return showKategoriMenu(msg, user, kategoriList);
            }

            userState[user] = "PRODUK";

            let reply = `🛒 *Produk ${kategori.nama_kategori}*\n\n`;
            produk.forEach((item, i) => {
                reply += `${i + 1}. ${item.nama_barang}\n`;
                reply += `   💰 Rp ${Number(item.harga_jual).toLocaleString('id-ID')}\n`;
                reply += `   📦 Stok : ${item.stok}\n\n`;
            });

            reply += "0️⃣ Kembali ke Kategori";
            reply += "\n✍️ Ketik *menu* untuk Menu Utama";

            await msg.reply(reply);
            return;
        }

        /* ================= PRODUK ================= */
        if (userState[user] === "PRODUK") {

            if (text === "0") {
                const kategori = userKategoriMap.get(user);
                return showKategoriMenu(msg, user, kategori);
            }

            if (text === "menu") {
                return showMainMenu(msg, user);
            }
        }

        /* ================= JASA ================= */
        if (userState[user] === "JASA" && text === "9") {
            return showMainMenu(msg, user);
        }

        /* ================= RIWAYAT ================= */
        if (userState[user] === "RIWAYAT_PLAT") {
            const plat = text;

            try {
                const res = await axios.get(
                    `https://bengkel.adsmotor.id/api/boot/costumer/${plat}`
                );

                if (res.success == false) {
                    await msg.reply("❌ Data tidak ditemukan.\n\n9️⃣ Menu Utama");
                    userState[user] = "MENU";
                    return;
                }

                const data = res.data.data;

                let reply = `📄 *Riwayat Service*\n\n`;
                reply += `👤 Nama  : ${data.nama}\n`;
                reply += `🚗 Plat  : ${data.plat}\n`;
                reply += `🧾 Bukti : ${data.bukti}\n\n`;

                if (!data.jasa || data.jasa.length === 0) {
                    reply += "📦 Belum ada riwayat jasa.\n\n";
                } else {
                    reply += "🔧 *Jasa yang Pernah Dilakukan:*\n";
                    data.jasa.forEach((jasa, i) => {
                        reply += `${i + 1}. ${jasa}\n`;
                    });
                    reply += "\n";
                }

                reply += "\n✍️ Ketik *menu* untuk Menu Utama";

                await msg.reply(reply);
                userState[user] = "MENU";
                return;

            } catch (err) {
                await msg.reply("⚠️ Data tidak ditemukan.\n\n✍️ Ketik *menu* untuk Menu Utama");
                userState[user] = "MENU";
                return;
            }
        }

        /* ================= ULASAN ================= */
        if (userState[user] === "ULASAN" && ["1", "2", "3", "4", "5"].includes(text)) {
            userState[user] = "ULASAN_KOMENTAR";
            await msg.reply("🙏 Terima kasih! Silakan tulis komentar Anda.");
            return;
        }

        if (userState[user] === "ULASAN_KOMENTAR") {
            userState[user] = "MENU";
            await msg.reply("✅ Terima kasih atas ulasan Anda!\n9️⃣ Menu Utama");
            return;
        }

        /* ================= DEFAULT ================= */
        await msg.reply("❓ Perintah tidak dikenali.\nKetik *menu*.");

    } catch (err) {
        console.error(err);
        await msg.reply("⚠️ Terjadi kesalahan sistem.");
    }
});



client.initialize();
