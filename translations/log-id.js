/*-----------------------------------
TEXT TRANSLATION SNIPPETS FOR GOBRIK.com
-----------------------------------*/

// Ampersand (&): Should be escaped as &amp; because it starts HTML character references.
// Less-than (<): Should be escaped as &lt; because it starts an HTML tag.
// Greater-than (>): Should be escaped as &gt; because it ends an HTML tag.
// Double quote ("): Should be escaped as &quot; when inside attribute values.
// Single quote/apostrophe ('): Should be escaped as &#39; or &apos; when inside attribute values.
// Backslash (\): Should be escaped as \\ in JavaScript strings to prevent ending the string prematurely.
// Forward slash (/): Should be escaped as \/ in </script> tags to prevent prematurely closing a script.


const id_Page_Translations = {
    "001-log-title": "Catat Ecobrick",
    "002-log-subheading": "Bagikan ecobrick Anda dengan dunia!<br>Gunakan formulir ini untuk mencatat ecobrick Anda ke dalam sistem brikchain.",
    "005-ecobricker-maker": "Siapa yang membuat ecobrick ini?",
    "005b-ecobricker-maker-caption": "Berikan nama pembuat ecobrick. Hindari karakter khusus.",
    "000-field-required-error": "Bidang ini wajib diisi.",
    "000-maker-field-too-long-error": "Nama terlalu panjang. Maksimal 255 karakter.",
    "005b-maker-error": "Entri ini mengandung karakter tidak valid. Hindari penggunaan tanda kutip, garis miring, dan tanda lebih besar dari.",
    "006-volume-ml": "Volume Ecobrick (dalam mililiter):",
    "006-volume-ml-caption": "Harap berikan volume ecobrick dalam mililiter.",
    "006-select-volume": "Pilih volume...",
    "007-weight-g": "Berat ecobrick (dalam gram):",
    "007-weight-g-caption": "Bulatkan ke gram terdekat.",
    "007-brand_name": "Merk botol apa yang digunakan untuk ecobrick ini?",
    "000-field-too-long-error": "Entri ini harus kurang dari 100 karakter. Yang kami butuhkan hanya nama merek botol, misalnya \"Max Water\".",
    "000-field-invalid-error": "Entri ini mengandung karakter tidak valid. Hindari penggunaan tanda kutip, garis miring, dan tanda lebih besar dari.",
    "008-bottom-color": "Warna dasar Ecobrick:",
    "008-bottom-color-caption": "Silakan pilih warna dasar ecobrick.",
    "009-sequestration-type": "Jenis ecobrick apa ini?",
    "009-sequestration-type-caption": "Silakan pilih jenis ecobrick. Pelajari lebih lanjut tentang <a href=\"#\" onclick=\"showModalInfo('ocean')\" class=\"underline-link\">ocean ecobricks</a>, <a href=\"#\" onclick=\"showModalInfo('cigbrick')\" class=\"underline-link\">cigbricks</a> dan <a href=\"#\" onclick=\"showModalInfo('regular')\" class=\"underline-link\">ecobrick biasa</a>.",
    "010-plastic-from": "Dari mana asal plastik ini?",
    "010-plastic-from-caption": "Dari mana plastik ecobrick Anda berasal?",
    "011-location-full": "Di mana ecobrick ini berada?",
    "011-location-full-caption": "Mulailah mengetik nama kota atau desa Anda, dan kami akan mengisi sisanya menggunakan API openstreetmaps yang bersumber terbuka dan non-korporat. Hindari penggunaan alamat lengkap Anda untuk privasi -- cukup kota, desa, atau negara Anda saja.",

    "009-select-bottom-color": "Pilih warna bawah...",
    "010-no-color-set": "Tidak ada warna yang ditentukan",
    "011-clear": "Bening",
    "012-white": "Putih",
    "013-black": "Hitam",
    "014-yellow": "Kuning",
    "015-orange": "Oranye",
    "016-red": "Merah",
    "017-pink": "Merah Muda",
    "018-purple": "Ungu",
    "019-violet": "Violet",
    "020-dark-blue": "Biru Tua",
    "021-sky-blue": "Biru Langit",
    "022-brown": "Coklat",
    "023-grey": "Abu-abu",
    "024-silver": "Perak",
    "025-gold": "Emas",
    "026-cigbrick-beige": "Beige Cigbrick",
    "000-field-required-error": "Bidang ini diperlukan.",

    "011-select-ecobrick-type": "Pilih jenis ecobrick...",
    "012-regular-ecobrick": "Ecobrick biasa",
    "013-cigbrick": "Cigbrick",
    "014-ocean-ecobrick": "Ecobrick laut",
    "015-select-plastic-source": "Pilih sumber plastik...",
    "016-home": "Rumah",
    "017-business": "Bisnis",
    "018-community": "Lingkungan",
    "019-factory": "Pabrik",
    "020-beach": "Pantai",
    "021-ocean": "Laut",
    "022-river": "Sungai",
    "023-forest": "Hutan",
    "024-field": "Ladang",


  '030-save-as-default': 'Simpan ini sebagai pengaturan ecobrick default saya.',
  '031-location-tags': '⚙️ Lokasi',
  '032-community-tag': 'Komunitas:',
  '032-watershed-tag': 'Daerah Aliran Sungai:',
  '033-location-tag': 'Lokasi:',
  '035-your-defaults-loaded': 'Pengaturan default Anda telah dimuat. 🫡',
  '111-localization-explanation': 'Saat Anda mencatat ecobrick, itu diberi tag dengan lokalisasi akun Buwana Anda. Anda dapat mengedit pengaturan default ini di sini:',


    "016-submit-button": '<input type="submit" class="submit-button enabled" value="Berikutnya: Periksa Kepadatan" aria-label="Kirim Formulir">',

//Modals for density check

    "underDensityTitle": "Kepadatan di Bawah Standar",
    "underDensityMessage": "Kepadatan ecobrick Anda sebesar ${density} berada di bawah standar GEA sebesar 0,33g/ml. Harap periksa apakah Anda telah memasukkan berat dan volume dengan benar. Jika tidak, maka mohon kemas ulang ecobrick Anda dengan lebih banyak plastik untuk mencapai kepadatan minimum. Pedoman GEA dikembangkan untuk memastikan integritas bangunan, keamanan kebakaran, dan kegunaan kembali ecobrick.",
    "lowDensityTitle": "Kepadatan Rendah",
    "lowDensityMessage": "Hati-hati! Kepadatan ecobrick Anda sebesar ${density}ml berada di sisi rendah. Ini memenuhi standar minimum sebesar 0,33g/ml, namun kepadatannya membuatnya kurang padat, kurang aman terhadap kebakaran, dan kurang dapat digunakan kembali. Teruskan dan catat ecobrick ini, tetapi lihat apakah Anda dapat mengemas lebih banyak plastik di waktu berikutnya.",
    "greatJobTitle": "Kerja bagus!",
    "greatJobMessage": "Kepadatan ecobrick Anda sebesar ${density} adalah ideal. Ini memenuhi standar minimum sebesar 0,33g/ml, membuatnya padat, aman terhadap kebakaran, dan dapat digunakan kembali.",
    "highDensityTitle": "Kepadatan Tinggi",
    "highDensityMessage": "Hati-hati, kepadatan ecobrick Anda sebesar ${density} sangat tinggi. Botol ${volume} Anda yang dikemas dengan ${weight} plastik berada di bawah kepadatan maksimum sebesar 0,73g/ml, namun kepadatan yang tinggi ini membuatnya hampir terlalu padat dan terlalu berat untuk beberapa aplikasi ecobrick. Teruskan, tetapi ingat ini untuk waktu berikutnya.",
    "overMaxDensityTitle": "Melebihi Kepadatan Maksimum",
    "overMaxDensityMessage": "Kepadatan ecobrick Anda sebesar ${density} melebihi standar GEA sebesar 0,73g/ml. Harap periksa apakah Anda telah memasukkan berat dan volume dengan benar. Jika demikian, maka mohon kemas ulang ecobrick Anda dengan lebih sedikit plastik. Pedoman GEA dikembangkan untuk memastikan keamanan dan kegunaan ecobrick untuk semua aplikasi jangka pendek dan jangka panjang.",
    "geaStandardsLinkText": "Standar GEA",
    "nextRegisterSerial": "Berikutnya: Daftar Nomor Seri",
    "goBack": "Kembali",

    };


