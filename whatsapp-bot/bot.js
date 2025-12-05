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
        headless: true,
        executablePath: '/usr/bin/google-chrome',
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--no-first-run',
            '--no-zygote',
            '--disable-gpu'
        ]
    }
});

client.on('qr', qr => {
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    console.log('✅ WhatsApp Bot ADS Motor siap tanpa scan ulang!');
});

client.on('message', async msg => {
    const text = msg.body.trim().toLowerCase();
    const user = msg.from;

    try {
        // MENU UTAMA
        if (["halo", "menu", "mulai", "hi", "hai"].includes(text)) {
            const menuText =
                `👋 *Selamat datang di ADS Motor!*

Berikut layanan yang tersedia:
1️⃣ Booking Service
2️⃣ Cek Promo
3️⃣ Beri Ulasan
4️⃣ Hubungi Admin

Ketik angka *1-4* untuk memilih menu.
Contoh: *1* untuk Booking Service.`;

            await msg.reply(menuText);
            return;
        }

        // === Pilihan Menu ===
        if (text === "1" || text.includes("booking")) {
            await msg.reply(
                `📅 *Booking Service*

Silakan kirim data Anda dengan format berikut:
*Nama - Plat Nomor - Tanggal Service (DD/MM/YYYY)*

Contoh:
Budi - DD1234AB - 15/11/2025`
            );
            return;
        }

        if (text === "2" || text.includes("promo")) {
            await msg.reply(
                `🎉 *Promo Spesial Bulan Ini dari ADS Motor!*

- 💧 Ganti oli gratis cuci motor
- 🔧 Diskon 15% servis lengkap
- 🛞 Gratis pengecekan rem & tekanan ban

Ketik *menu* untuk kembali ke menu utama.`
            );
            return;
        }

        if (text === "3" || text.includes("ulasan") || text.includes("review")) {
            await msg.reply(
                `⭐ Kami ingin tahu pengalaman Anda!
Beri rating untuk layanan kami:

Ketik angka:
5️⃣ Sangat Puas
4️⃣ Puas
3️⃣ Cukup
2️⃣ Kurang
1️⃣ Buruk`
            );
            return;
        }

        if (["1", "2", "3", "4", "5"].includes(text)) {
            const ratingText = {
                "5": "⭐️⭐️⭐️⭐️⭐️ Sangat Puas",
                "4": "⭐️⭐️⭐️⭐️ Puas",
                "3": "⭐️⭐️⭐️ Cukup",
                "2": "⭐️⭐️ Kurang",
                "1": "⭐️ Sangat Buruk"
            };
            await msg.reply(
                `🙏 Terima kasih atas ulasan Anda: *${ratingText[text]}*.
                Silakan tulis komentar tambahan (opsional),
                atau ketik *menu* untuk kembali.`
            );
            return;
        }

        if (text === "4" || text.includes("admin")) {
            await msg.reply(
                `📞 *Hubungi Admin ADS Motor*

Anda dapat menghubungi kami di:
📱 *0812-3456-7890* (Chat / Telepon)

Atau ketik *menu* untuk kembali ke menu utama.`
            );
            return;
        }

        // Format booking (nama - plat - tanggal)
        if (text.includes("-") && text.split("-").length >= 3) {
            await msg.reply(
                `✅ Terima kasih! Data booking Anda sudah kami terima.
Tim *ADS Motor* akan segera menghubungi Anda untuk konfirmasi jadwal service.

🧾 Data Anda:
${msg.body}

Ketik *menu* untuk kembali ke menu utama.`
            );
            return;
        }

        // Komentar tambahan setelah review
        if (text.length > 5 && !["menu", "halo"].includes(text)) {
            await msg.reply(
                `📩 Terima kasih atas feedback Anda!
Kami akan terus meningkatkan pelayanan di *ADS Motor* 🚗💨

Ketik *menu* untuk kembali.`
            );
            return;
        }

    } catch (error) {
        const msgError = error.response ? JSON.stringify(error.response.data) : error.message;
        await msg.reply("⚠️ Terjadi kesalahan:\n" + msgError);
    }
});

// =========================
// API UNTUK KIRIM STRUK WA
// =========================
const express = require("express");
const app = express();
app.use(express.json());

app.post('/kirim-struk', async (req, res) => {
    const { nomor, pesan } = req.body;
    console.log(nomor, pesan);


    try {
        await client.sendMessage(`${nomor}@c.us`, pesan);
        res.json({ status: "success", message: "Struk terkirim ke WA" });
    } catch (err) {
        res.json({ status: "error", message: err.message });
    }
});

app.listen(5000, () => {
    console.log("🚀 WhatsApp Bot API berjalan di http://localhost:5000");
});

client.initialize();
