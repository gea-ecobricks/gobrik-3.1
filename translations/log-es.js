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

const es_Page_Translations = {
    "001-log-title": "Registrar un Ecobrick",
    "002-log-subheading": "¡Comparte tu ecobrick con el mundo!<br>Usa este formulario para registrar tu ecobrick en el sistema brikchain.",
    "005-ecobricker-maker": "¿Quién hizo este ecobrick?",
    "005b-ecobricker-maker-caption": "Proporcione el nombre del ecobricker. Evite caracteres especiales.",
    "000-field-required-error": "Este campo es obligatorio.",
    "000-maker-field-too-long-error": "El nombre es demasiado largo. Máximo 255 caracteres.",
    "005b-maker-error": "La entrada contiene caracteres no válidos. Evite las comillas, barras y signos de mayor que, por favor.",
    "006-volume-ml": "Volumen del Ecobrick (en mililitros):",
    "006-select-volume": "Seleccionar volumen...",
    "006-volume-ml-caption": "Proporcione el volumen del ecobrick en mililitros.",
    "007-weight-g": "Peso del Ecobrick (en gramos):",
    "007-weight-g-caption": "Redondear al gramo más cercano.",
    "007-brand_name": "¿Qué marca de botella se utiliza para este ecobrick?",
    "000-field-too-long-error": "Esta entrada debe tener menos de 100 caracteres. Solo necesitamos el nombre de la marca de la botella, p. ej., \"Max Water\".",
    "000-field-invalid-error": "La entrada contiene caracteres no válidos. Evite las comillas, barras y signos de mayor que, por favor.",
    "008-bottom-color": "Color de la base del Ecobrick:",
    "008-bottom-color-caption": "Seleccione el color de la base del ecobrick.",
    "009-sequestration-type": "¿Qué tipo de ecobrick es este?",
    "009-sequestration-type-caption": "Seleccione el tipo de ecobrick. Aprende más sobre <a href=\"#\" onclick=\"showModalInfo('ocean')\" class=\"underline-link\">ecobricks oceánicos</a>, <a href=\"#\" onclick=\"showModalInfo('cigbrick')\" class=\"underline-link\">cigbricks</a> y <a href=\"#\" onclick=\"showModalInfo('regular')\" class=\"underline-link\">ecobricks regulares</a>.",
    "010-plastic-from": "¿De dónde proviene el plástico?",
    "010-plastic-from-caption": "¿De dónde proviene el plástico de tu ecobrick?",
    "011-location-full": "¿Dónde se encuentra este ecobrick?",
    "011-location-full-caption": "Empieza a escribir el nombre de tu ciudad o pueblo, y completaremos el resto utilizando la API de openstreetmaps, abierta y no corporativa. Evita usar tu dirección exacta por privacidad: solo tu ciudad, pueblo o país está bien.",

    "009-select-bottom-color": "Seleccionar color de la base...",
    "010-no-color-set": "No se ha establecido un color deliberado",
    "011-clear": "Claro",
    "012-white": "Blanco",
    "013-black": "Negro",
    "014-yellow": "Amarillo",
    "015-orange": "Naranja",
    "016-red": "Rojo",
    "017-pink": "Rosa",
    "018-purple": "Morado",
    "019-violet": "Violeta",
    "020-dark-blue": "Azul oscuro",
    "021-sky-blue": "Azul cielo",
    "022-brown": "Marrón",
    "023-grey": "Gris",
    "024-silver": "Plateado",
    "025-gold": "Dorado",
    "026-cigbrick-beige": "Beige cigbrick",
    "000-field-required-error": "Este campo es obligatorio.",

    "011-select-ecobrick-type": "Seleccionar tipo de ecobrick...",
    "012-regular-ecobrick": "Ecobrick regular",
    "013-cigbrick": "Cigbrick",
    "014-ocean-ecobrick": "Ecobrick oceánico",
    "015-select-plastic-source": "Seleccionar fuente de plástico...",
    "016-home": "Hogar",
    "017-business": "Negocio",
    "018-community": "Vecindario",
    "019-factory": "Fábrica",
    "020-beach": "Playa",
    "021-ocean": "Océano",
    "022-river": "Río",
    "023-forest": "Bosque",
    "024-field": "Campo",

    "016-submit-button": '<input type="submit" class="submit-button enabled" value="Siguiente: Verificación de Densidad" aria-label="Enviar Formulario">',

  '030-save-as-default': 'Guardar esto como mis configuraciones predeterminadas de ecoladrillo.',
  '031-location-tags': '⚙️ Ubicación',
  '032-community-tag': 'Comunidad:',
  '032-watershed-tag': 'Cuenca:',
  '033-location-tag': 'Ubicación:',
  '035-your-defaults-loaded': 'Se han cargado tus configuraciones predeterminadas. 🫡',
  '111-localization-explanation': 'Cuando registras un ecoladrillo, se etiqueta con la localización de tu cuenta Buwana. Puedes editar estos valores predeterminados aquí:',

//Modals for density check

    "underDensityTitle": 'Baja Densidad',
    "underDensityMessage": "La densidad de tu ecobrick de ${density} está por debajo del estándar GEA de 0.33g/ml. Por favor, verifica que hayas ingresado el peso y el volumen correctamente. Si no, empaca tu ecobrick con más plástico para alcanzar la densidad mínima. Las pautas de GEA están desarrolladas para garantizar la integridad estructural, seguridad contra incendios y reutilizabilidad de un ecobrick.",
    "lowDensityTitle": 'Densidad Baja',
    "lowDensityMessage": "¡Cuidado! La densidad de tu ecobrick de ${density}ml está en el lado bajo. Cumple con el estándar mínimo de 0.33g/ml, sin embargo, su baja densidad lo hace menos sólido, seguro contra incendios y reutilizable de lo que podría ser. Continúa y registra este ecobrick, pero intenta empacar más plástico la próxima vez.",
    "greatJobTitle": '¡Buen trabajo!',
    "greatJobMessage": "La densidad de tu ecobrick de ${density} es ideal. Cumple con el estándar mínimo de 0.33g/ml, lo que lo hace sólido, seguro contra incendios y reutilizable.",
    "highDensityTitle": 'Alta Densidad',
    "highDensityMessage": "Cuidado, la densidad de tu ecobrick de ${density} es muy alta. Tu botella de ${volume} empacada con ${weight} de plástico está por debajo de la densidad máxima de 0.73g/ml; sin embargo, su alta densidad lo hace casi demasiado sólido y demasiado pesado para ciertas aplicaciones de ecobricks. Continúa, pero ten esto en cuenta para la próxima vez.",
    "overMaxDensityTitle": 'Sobre la Densidad Máxima',
    "overMaxDensityMessage": "La densidad de tu ecobrick de ${density} está por encima del estándar GEA de 0.73g/ml. Por favor, verifica que hayas ingresado el peso y el volumen correctamente. Si es así, vuelve a empacar tu ecobrick con menos plástico. Las pautas de GEA están desarrolladas para garantizar la seguridad y usabilidad de los ecobricks para todas las aplicaciones a corto y largo plazo.",
    "geaStandardsLinkText": 'Estándares GEA',
    "nextRegisterSerial": 'Siguiente: Registrar Serial',
    "goBack": 'Regresar',
};

