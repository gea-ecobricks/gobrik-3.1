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

  "001-form-title": "Catat Nomor Seri & Ambil Foto",
  "002-form-description-1": "Ecobrick Anda telah dicatat dengan berat ",
  "003-form-description-2": "volume ",
  "004-form-description-3": "dan kepadatan ",
  "005-form-description-4": " Ecobrick Anda telah diberikan nomor seri:",
  "006-enscribe-label": "Bagaimana Anda ingin menuliskan nomor seri di ecobrick Anda?",
  "007-enscribe-option-1": "Pilih satu...",
  "008-enscribe-option-2": "Spidol permanen",
  "009-enscribe-option-3": "Spidol yang larut dalam air 👎",
  "010-enscribe-option-4": "Cat enamel",
  "011-enscribe-option-5": "Cat kuku",
  "012-enscribe-option-6": "Sisipan plastik",
  "013-enscribe-option-7": "Lainnya",
  "014-photo-options-label": "Jenis foto apa yang ingin Anda catat untuk ecobrick Anda?",
  "015-photo-options-option-1": "Pilih satu...",
  "016-photo-options-option-2": "Foto ecobrick dasar",
  "017-photo-options-option-3": "Foto selfie",
  "018-photo-options-option-4": "Foto dasar dan foto selfie",
  "019-feature-photo": "Unggah foto ecobrick dasar:",
  "020-feature-photo-step-1": "Ambil foto potret vertikal",
  "021-feature-photo-step-2": "Pastikan foto Anda menampilkan nomor seri & berat dengan jelas",
  "022-feature-photo-step-3": "Pastikan foto Anda menunjukkan warna bagian bawah ecobrick Anda",
  "023-feature-photo-step-4": "Pastikan foto Anda menunjukkan bagian atas ecobrick Anda",
  "024-feature-photo-step-5": "Pastikan data Anda tertulis secara permanen!",
  "025-feature-photo-step-6": "Jangan gunakan label eksternal untuk menandai ecobrick",
//  "025-basic-photo-label": '📷 Ambil Foto Dasar<input type="file" id="ecobrick_photo_main" name="ecobrick_photo_main" onchange="displayFileName()">,
//  "026-basic-feature-desc": "Ambil atau pilih foto ecobrick Anda yang telah bernomor seri.",
  "027-label-selfie": "Unggah selfie ecobrick:",
  "028-selfie-photo-step-1": "Pastikan foto Anda adalah lanskap horizontal",
  "029-selfie-photo-step-2": "Pastikan foto Anda menampilkan nomor seri & berat dengan jelas",
  "030-selfie-photo-step-3": "Pastikan foto Anda menunjukkan warna bagian bawah ecobrick Anda",
  "031-selfie-photo-step-4": "Pastikan foto Anda menunjukkan bagian atas ecobrick Anda",
  "032-selfie-photo-step-5": "Pastikan data Anda tertulis secara permanen!",
  "033-selfie-photo-step-6": "Jangan gunakan label eksternal untuk menandai ecobrick",
  "034-selfie-photo-step-7": "Dan tersenyum!",
//  "035-selfie-upload": '📷 Ambil Foto Selfie<input type="file" id="selfie_photo_main" name="selfie_photo_main">',
//  "035b-no-file-chosen": "Tidak ada file yang dipilih",
  "036-another-photo-optional": "Unggah selfie ecobrick Anda.",
  "037-submit-upload-button": '<input type="submit" value="⬆️ Unggah Foto" id="upload-progress-button" aria-label="Kirim foto untuk diunggah">',


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

     "inserts-title": "Sisipan Plastik",
    "inserts-text": "Untuk daya tahan nomor seri yang maksimal, tulis nomor seri Anda pada selembar plastik putih kaku menggunakan spidol permanen dan masukkan ke dalam ecobrick yang sudah selesai.",

    "nailvarnish-title": "Pernis Kuku",
    "nailvarnish-text": "Tulis nomor seri menggunakan botol pernis kuku lama.",

    "enamel-title": "Cat Enamel",
    "maker-text": "Tulis nomor seri menggunakan kuas cat dan cat minyak/enamel.",

    "marker-title": "Spidol Permanen",
    "maker-text": "Tulis nomor seri menggunakan spidol permanen (bukan spidol papan atau spidol berbasis air).",

    "035-selfie-upload-box": '<div class="photo-upload-container" data-lang-id="035-selfie-upload-box"><label for="selfie_photo_main" class="custom-file-upload">📷 Tambahkan Foto Selfie <input type="file" id="selfie_photo_main" name="selfie_photo_main" onchange="displayFileName(\'selfie_photo_main\', \'file-name-selfie\')"></label><span id="file-name-selfie" class="file-name">Tidak ada file yang dipilih</span><p class="form-caption">Unggah selfie ecobrick Anda.</p></div>',
    "013b-see-examples": '👁️ Lihat contoh <a href="#" onclick="showModalInfo(\'inserts\')" class="underline-link">sisipan plastik</a>, <a href="#" onclick="showModalInfo(\'enamel\')" class="underline-link">cat enamel</a>, <a href="#" onclick="showModalInfo(\'marker\')" class="underline-link">spidol permanen</a> dan <a href="#" onclick="showModalInfo(\'nailvarnish\')" class="underline-link">pernis kuku</a>',



    };
