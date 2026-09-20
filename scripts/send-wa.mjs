#!/usr/bin/env node

/**
 * EduAttend Node.js WhatsApp Notification Service
 * Sends automated attendance notifications (Masuk / Pulang) via WhatsApp API.
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Helper to parse CLI flags (e.g., --phone=0812345 --type=MASUK)
function parseArgs() {
    const args = {};
    process.argv.slice(2).forEach(arg => {
        if (arg.startsWith('--')) {
            const [key, ...valueParts] = arg.replace(/^--/, '').split('=');
            args[key] = valueParts.join('=');
        }
    });
    return args;
}

const flags = parseArgs();

const phone   = flags.phone || flags.to || '';
const type    = (flags.type || 'MASUK').toUpperCase();
const name    = flags.name || 'Siswa';
const kelas   = flags.kelas || '-';
const time    = flags.time || new Date().toLocaleTimeString('id-ID');
const date    = flags.date || new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
const status  = flags.status || 'Hadir';
const lokasi  = flags.lokasi || 'GPS/Sekolah';
const gateway = flags.gateway || process.env.WA_GATEWAY_URL || 'https://api.fonnte.com/send-message';
const token   = flags.token || process.env.WA_API_TOKEN || '';

const logPath = path.join(__dirname, '../storage/logs/wa.log');

function log(msg) {
    const timeStamp = new Date().toISOString();
    const line = `[${timeStamp}] ${msg}\n`;
    console.log(line.trim());
    try {
        fs.appendFileSync(logPath, line);
    } catch (e) {
        // ignore log write errors
    }
}

if (!phone) {
    log('ERROR: No target phone number specified (--phone=...)');
    process.exit(1);
}

// Format Phone Number to International Indonesian Format (e.g. 0812 -> 62812)
function formatPhone(num) {
    let cleaned = num.replace(/\D/g, '');
    if (cleaned.startsWith('0')) {
        cleaned = '62' + cleaned.slice(1);
    }
    return cleaned;
}

const formattedPhone = formatPhone(phone);

// Build Message Template
let message = '';
if (type === 'MASUK') {
    message = `*[SAMAYA] NOTIFIKASI PRESENSI MASUK*

Halo, *${name}* (Kelas: ${kelas})
Presensi *MASUK* Anda telah berhasil dicatat.

Tanggal : *${date}*
Waktu   : *${time}*
Status  : *${status}*
Lokasi  : ${lokasi}

Terima kasih telah melakukan presensi tepat waktu. Selamat belajar!`;
} else if (type === 'PULANG') {
    message = `*[SAMAYA] NOTIFIKASI PRESENSI PULANG*

Halo, *${name}* (Kelas: ${kelas})
Presensi *PULANG* Anda telah berhasil dicatat.

Tanggal : *${date}*
Waktu   : *${time}*
Lokasi  : ${lokasi}

Hati-hati di jalan dan selamat beristirahat di rumah!`;
} else if (type === 'WARNING_TELAT' || type === 'TELAT') {
    message = `*[SAMAYA] PERINGATAN KETERLAMBATAN PRESENSI*

Halo, *${name}* (Kelas: ${kelas})
Waktu batas jam masuk (*${time}*) telah terlewati dan Anda belum melakukan presensi hari ini (*${date}*).

Harap segera melakukan presensi sebelum jam batas Alfa.`;
} else if (type === 'ALFA') {
    message = `*[SAMAYA] NOTIFIKASI PRESENSI ALFA*

Halo, *${name}* (Kelas: ${kelas})
Anda tercatat *ALFA* (Tanpa Keterangan) pada hari ini, *${date}*, karena telah melewati jam batas presensi (*${time}*) dan belum melakukan presensi.

Jika terdapat kekeliruan, silakan hubungi Wali Kelas / Sekolah.`;
} else {
    message = `*[SAMAYA] NOTIFIKASI PRESENSI*

Halo, *${name}* (Kelas: ${kelas})
Tanggal : *${date}*
Waktu   : *${time}*
Status  : *${status}*`;
}

log(`SENDING WA to [${formattedPhone}] (${type}) - Name: ${name}`);

// Send Request via Fetch API
async function sendWhatsApp() {
    if (!token) {
        log(`[SIMULATION MODE] Token WA API tidak dikonfigurasi. Pesan berikut siap dikirim ke ${formattedPhone}:\n---\n${message}\n---`);
        process.exit(0);
    }

    try {
        const response = await fetch(gateway, {
            method: 'POST',
            headers: {
                'Authorization': token,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                target: formattedPhone,
                message: message,
                countryCode: '62',
            }),
        });

        const resultText = await response.text();
        log(`WA API RESPONSE (${response.status}): ${resultText}`);
    } catch (error) {
        log(`ERROR Sending WA: ${error.message}`);
        process.exit(1);
    }
}

sendWhatsApp();
