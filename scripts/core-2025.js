function openProfile() {
    window.location.href = 'profile.php';
}

function logoutUser() {
    const path = window.location.pathname.split("/").pop() + window.location.search;
    const redirectUrl = encodeURIComponent(path);
    window.location.href = `logout.php?redirect=${redirectUrl}`;
}


// Safely escape HTML for dynamic content
function escapeHTML(str) {
    if (str === undefined || str === null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}






function redirectToWelcome() {
    window.location.href = "index.php";
}

document.addEventListener("scroll", function() {
    var scrollPosition = window.scrollY || document.documentElement.scrollTop;

    // Check if the user has scrolled more than 1000px
    if (scrollPosition > 1000) {
        var footer = document.getElementById('footer-full');
        if (footer) {
            footer.style.display = 'block'; // Show the footer
        }
    }
});




/* RIGHT SETTINGS OVERLAY */

function openSideMenu() {
  document.getElementById("main-menu-overlay").style.width = "100%";
  document.getElementById("main-menu-overlay").style.display = "block";
  document.body.style.overflowY = "hidden";
  document.body.style.maxHeight = "101vh";

  var modal = document.getElementById('main-menu-overlay');

function modalShow () {
   modal.setAttribute('tabindex', '0');
   modal.focus();
}

function focusRestrict ( event ) {
  document.addEventListener('focus', function( event ) {
    if ( modalOpen && !modal.contains( event.target ) ) {
      event.stopPropagation();
      modal.focus();
    }
  }, true);
}
}

/* Close when someone clicks on the "x" symbol inside the overlay */
function closeSettings() {
  document.getElementById("main-menu-overlay").style.width = "0%";
  document.body.style.overflowY = "unset";
document.body.style.maxHeight = "unset";
  //document.body.style.height = "unset";
} 

function modalCloseCurtains ( e ) {
  if ( !e.keyCode || e.keyCode === 27 ) {
    
  document.body.style.overflowY = "unset";
  document.getElementById("main-menu-overlay").style.width = "0%";
  /*document.getElementById("knack-overlay-curtain").style.height = "0%";*/

  }
}


// 2025 TOP RIGHT SETTINGS PANEL


document.addEventListener('DOMContentLoaded', () => {
    const settingsButton = document.getElementById('top-settings-button');
    const settingsPanel = document.getElementById('settings-buttons');
    const langMenu = document.getElementById('language-menu-slider');
    const loginMenu = document.getElementById('login-menu-slider');
    const header = document.getElementById('header');

    let settingsOpen = false;

    // 🔄 Update header background and z-index based on menu visibility
    function updateHeaderVisuals() {
        const langVisible = langMenu.classList.contains('menu-slider-visible');
        const loginVisible = loginMenu.classList.contains('menu-slider-visible');

        if (langVisible || loginVisible) {
            header.style.background = 'var(--top-header)';
            header.style.zIndex = '36';

            if (langVisible) {
                langMenu.style.zIndex = '35';
            } else {
                langMenu.style.zIndex = '18'; // reset if not visible
            }

            if (loginVisible) {
                loginMenu.style.zIndex = '35';
            } else {
                loginMenu.style.zIndex = '19'; // reset if not visible
            }

        } else {
            header.style.background = 'none';
            header.style.zIndex = '20';
            langMenu.style.zIndex = '18';
            loginMenu.style.zIndex = '19';
        }
    }



    // 🔁 Toggle settings panel
    window.toggleSettingsMenu = () => {
        settingsOpen = !settingsOpen;
        settingsPanel.classList.toggle('open', settingsOpen);
        settingsButton.setAttribute('aria-expanded', settingsOpen ? 'true' : 'false');

        hideLangSelector();
        hideLoginSelector();
    };

    // 🌐 Toggle language selector
    window.showLangSelector = () => {
        const isVisible = langMenu.classList.contains('menu-slider-visible');
        hideLoginSelector();

        if (isVisible) {
            hideLangSelector();
        } else {
            langMenu.classList.add('menu-slider-visible');
            langMenu.style.maxHeight = '400px'; // or whatever max height fits your menu
            langMenu.style.overflow = 'hidden';
            langMenu.style.transition = 'max-height 0.4s ease';

            document.addEventListener('click', documentClickListenerLang);
            updateHeaderVisuals(); // ✅ Apply background and z-index
        }
    };

    window.hideLangSelector = () => {
        langMenu.classList.remove('menu-slider-visible');
        document.removeEventListener('click', documentClickListenerLang);
        updateHeaderVisuals(); // ✅ Update visuals
    };

    function documentClickListenerLang(e) {
        if (!langMenu.contains(e.target) && e.target.id !== 'language-code') {
            hideLangSelector();
        }
    }

    // 🔐 Toggle login selector
    window.showLoginSelector = () => {
        const isVisible = loginMenu.classList.contains('menu-slider-visible');
        hideLangSelector();

        if (isVisible) {
            hideLoginSelector();
        } else {
            loginMenu.classList.add('menu-slider-visible');
            loginMenu.style.maxHeight = '400px';
            loginMenu.style.overflow = 'hidden';
            loginMenu.style.transition = 'max-height 0.4s ease';

            document.addEventListener('click', documentClickListenerLogin);
            updateHeaderVisuals(); // ✅ Apply background and z-index
        }
    };

    window.hideLoginSelector = () => {
        if (loginMenu.classList.contains('menu-slider-visible')) {
            loginMenu.classList.remove('menu-slider-visible');
            document.removeEventListener('click', documentClickListenerLogin);
            updateHeaderVisuals(); // ✅ Update visuals when hidden
        }
    };

    function documentClickListenerLogin(e) {
        if (!loginMenu.contains(e.target) && !e.target.classList.contains('top-login-button')) {
            hideLoginSelector();
        }
    }


    // ✋ Click outside to close settings
    document.addEventListener('click', (e) => {
        if (!settingsPanel.contains(e.target) && e.target !== settingsButton) {
            settingsPanel.classList.remove('open');
            settingsOpen = false;
            settingsButton.setAttribute('aria-expanded', 'false');
        }
    });

    // Prevent menu closure on internal click
    settingsPanel.addEventListener('click', (e) => {
        e.stopPropagation();
    });
});

// 🔻 Hide dropdowns on scroll
window.addEventListener('scroll', () => {
    hideLangSelector();
    hideLoginSelector();
});



// 🌐 Hide language selector with a slide-up animation
function hideLangSelector() {
    if (!langMenu) return;

    if (langMenu.classList.contains('menu-slider-visible')) {
        langMenu.style.maxHeight = '0';
        langMenu.style.overflow = 'hidden';
        langMenu.style.transition = 'max-height 0.4s ease';

        setTimeout(() => {
            langMenu.classList.remove('menu-slider-visible');
            langMenu.style.removeProperty('max-height');
            langMenu.style.removeProperty('overflow');
            langMenu.style.removeProperty('transition');
            updateHeaderVisuals(); // ✅ Update after animation
        }, 400);
    }

    document.removeEventListener('click', documentClickListenerLang);
}




// 2024 FUNCTIONS









function goBack() {
    window.history.back();
}




document.querySelectorAll('.x-button').forEach(button => {
    button.addEventListener('transitionend', (e) => {
        // Ensure the transitioned property is the transform to avoid catching other transitions
        if (e.propertyName === 'transform') {
            // Check if the button is still being hovered over
            if (button.matches(':hover')) {
                button.style.backgroundImage = "url('../svgs/x-button-night-over.svg?v=3')";
            }
        }
    });

    // Optionally, revert to the original background image when not hovering anymore
    button.addEventListener('mouseleave', () => {
        button.style.backgroundImage = "url('../svgs/x-button-night.svg?v=3')";
    });
});



//ECOBRICK MODAL PREVIEW

function ecobrickPreview(imageUrl, brik_serial, weight, owner, location) {
    const modal = document.getElementById('form-modal-message');
    const contentBox = modal.querySelector('.modal-content-box'); // This is the part we want to hide
    const photoBox = modal.querySelector('.modal-photo-box'); // This is where we'll show the image
    const photoContainer = modal.querySelector('.modal-photo'); // The container for the image

    // Hide the content box and show the photo box
    contentBox.style.display = 'none'; // Hide the content box
    photoBox.style.display = 'block'; // Make sure the photo box is visible

    // Clear previous images from the photo container
    photoContainer.innerHTML = '';

    // Create and append the ecobrick image to the photo container
    var img = document.createElement('img');
    img.src = imageUrl;
    img.alt = "Ecobrick " + brik_serial;
    img.style.maxWidth = '90%';
    img.style.maxHeight = '75vh';
    img.style.minHeight = "400px";
    img.style.minWidth = "400px";
    img.style.margin = 'auto';
    // img.style.backgroundColor = '#8080802e'; hmmm
    photoContainer.appendChild(img);

    // Add ecobrick details and view details button inside photo container
    var details = document.createElement('div');
    details.className = 'ecobrick-details';
    details.innerHTML = '<p>Ecobrick ' + brik_serial + ' | ' + weight + ' of plastic sequestered by ' + owner + ' in ' + location + '.</p>' +
                        '<a href="brik.php?serial_no=' + brik_serial + '" class="preview-btn" style="margin-bottom: 50px;height: 25px;padding: 5px;border: none;padding: 5px 12px;">ℹ️ View Full Details</a>';
    photoContainer.appendChild(details);

    // Hide other parts of the modal that are not used for this preview
    modal.querySelector('.modal-content-box').style.display = 'none'; // Assuming this contains elements not needed for this preview

    // Show the modal
    modal.style.display = 'flex';

    //Blur out background
    document.getElementById('page-content')?.classList.add('blurred');
    document.getElementById('footer-full')?.classList.add('blurred');
    document.body.classList.add('modal-open');
}



/* ---------- ------------------------------

SCROLL CONTROL

-------------------------------------------*/
let lastScrollTop = 0;

window.onscroll = function() {
    scrollLessThan30();
    scrollMoreThan30();
    scrollMoreThan800();
    scrollLessThan800();
};

function scrollLessThan30() {
    if (window.pageYOffset <= 30) {
//        document.getElementById("header").style.height = "85px";
        document.getElementById("header").style.borderBottom = "none";
        document.getElementById("header").style.boxShadow = "none";
//        document.getElementById("gea-logo").style.width = "190px";
//        document.getElementById("gea-logo").style.height = "40px";
        document.getElementById("logo-gobrik").style.opacity = "1";
//        document.getElementById("header").style.top = "0";
//        document.getElementById("settings-buttons").style.padding = "16px 43px 16px 12px";
//        document.getElementById("language-menu-slider").style.top = "-15px";
//        document.getElementById("login-menu-slider").style.top = "-15px";

        // Set zIndex for the top banner image
        var topPageImage = document.querySelector('.top-page-image');
        if (topPageImage) {
            topPageImage.style.zIndex = "35";
        }
    }
}

function scrollMoreThan30() {
    if (window.pageYOffset > 30 && window.pageYOffset < 800) {
//        document.getElementById("header").style.height = "60px";
        document.getElementById("header").style.borderBottom = "var(--header-accent) 0.5px solid";
        document.getElementById("header").style.boxShadow = "0px 0px 15px rgba(0, 0, 10, 0.805)";
        document.getElementById("gea-logo").style.width = "170px";
        document.getElementById("gea-logo").style.height = "35px";
        document.getElementById("logo-gobrik").style.opacity = "0.9";
//        document.getElementById("settings-buttons").style.padding = "12px 43px 10px 12px";
//        document.getElementById("language-menu-slider").style.top = "-35px";
//        document.getElementById("login-menu-slider").style.top = "-35px";

        // Tuck the top banner image under the header
        var topPageImage = document.querySelector('.top-page-image');
        if (topPageImage) {
            topPageImage.style.zIndex = "25";
        }
    }
}

function scrollMoreThan800() {
    if (window.pageYOffset >= 800) {
        // Hide the header completely
        document.getElementById("header").style.top = "-140px";
    }
}

function scrollLessThan800() {
    if (window.pageYOffset < 800) {
        // Show the header again
        document.getElementById("header").style.top = "0";
    }
}


/* ---------- ------------------------------
TOGGLE PASSWORD VISIBILITY
-------------------------------------------*/


document.addEventListener("DOMContentLoaded", function() {
    // Select all elements with the class 'toggle-password'
    const togglePasswordIcons = document.querySelectorAll('.toggle-password');

    togglePasswordIcons.forEach(function(icon) {
        icon.addEventListener('click', function() {
            // Find the associated input field using the 'toggle' attribute
            const input = document.querySelector(icon.getAttribute('toggle'));
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.textContent = '🙉'; // 🔓 Change to unlocked emoji
                } else {
                    input.type = 'password';
                    icon.textContent = '🙈'; // 🔒 Change to locked emoji
                }
            }
        });
    });
});



/*-------------------------------------------


 SCRIPTS FOR ONCE LOGGED IN


-------------------------------------------*/

// function handleLogout(event) {
//     event.preventDefault(); // Prevent default link behavior
//
//     // Perform logout via AJAX
//     fetch(event.target.href)
//         .then(response => {
//             if (response.ok) {
//                 // Redirect to the login page with the appropriate parameters
//                 window.location.href = response.url;
//             } else {
//                 console.error('Failed to log out:', response.statusText);
//             }
//         })
//         .catch(error => {
//             console.error('Error during logout:', error);
//         });
// }
//
//
// // Function to handle the shaking animation
//     function shakeElement(element) {
//         element.classList.add('shake');
//         setTimeout(() => element.classList.remove('shake'), 400);
//     }




// Close notices when X is clicked
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.notice-close').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const notice = this.closest('.top-container-notice');
            if (notice) {
                notice.style.display = 'none';
            }
        });
    });
});

// -----------------------------------------------
// Shared community functions
// -----------------------------------------------
document.addEventListener('DOMContentLoaded', function() {
    window.showCommunitySuggestions = function(communities, suggestionsBox, nameInput, idInput) {
        suggestionsBox.innerHTML = '';
        communities.forEach(function(comm) {
            const item = document.createElement('div');
            item.textContent = comm.com_name;
            item.classList.add('suggestion-item');
            item.addEventListener('click', function() {
                nameInput.value = comm.com_name;
                idInput.value = comm.community_id;
                suggestionsBox.innerHTML = '';
            });
            suggestionsBox.appendChild(item);
        });
    };

    window.openAddCommunityModal = function() {
        const modal = document.getElementById('form-modal-message');
        const modalBox = document.getElementById('modal-content-box');

        modal.style.display = 'flex';
        modalBox.style.flexFlow = 'column';
        document.getElementById('page-content')?.classList.add('blurred');
        document.getElementById('footer-full')?.classList.add('blurred');
        document.body.classList.add('modal-open');

        modalBox.style.maxHeight = '100vh';
        modalBox.style.overflowY = 'auto';

        const countryOptions = (window.countries || []).map(c => `<option value="${c.country_id}">${c.country_name}</option>`).join('');
        const languageOptions = (window.languages || []).map(l => `<option value="${l.language_id}">${l.languages_native_name}</option>`).join('');

        modalBox.innerHTML = `
            <h4 style="text-align:center;" data-lang-id="014-add-community-title">Add Your Community</h4>
            <p data-lang-id="015-add-community-desc">Add your community to Buwana so that others can connect across regenerative apps.</p>
            <form id="addCommunityForm" onsubmit="addCommunity2Buwana(event)">
                <label for="newCommunityName" data-lang-id="016-community-name-label">Name of Community:</label>
                <input type="text" id="newCommunityName" name="newCommunityName" required>
                <label for="newCommunityType" data-lang-id="017-community-type-label">Type of Community:</label>
                <select id="newCommunityType" name="newCommunityType" required>
                    <option value="" data-lang-id="018-select-type-option">Select Type</option>
                    <option value="neighborhood" data-lang-id="019-type-neighborhood">Neighborhood</option>
                    <option value="city" data-lang-id="020-type-city">City</option>
                    <option value="school" data-lang-id="021-type-school">School</option>
                    <option value="organization" data-lang-id="022-type-organization">Organization</option>
                </select>
                <label for="communityCountry" data-lang-id="023-country-label">Country:</label>
                <select id="communityCountry" name="communityCountry" required>
                    <option value="" data-lang-id="024-select-country-option">Select Country...</option>
                    ${countryOptions}
                </select>
                <label for="communityLanguage" data-lang-id="025-language-label">Preferred Language:</label>
                <select id="communityLanguage" name="communityLanguage" required>
                    <option value="" data-lang-id="026-select-language-option">Select Language...</option>
                    ${languageOptions}
                </select>
                <button type="submit" style="margin-top:10px;" class="confirm-button enabled" data-lang-id="027-submit-button">Create Community</button>
            </form>
        `;

        if (typeof applyTranslations === 'function') {
            applyTranslations();
        }

        setTimeout(() => {
            const ctry = document.getElementById('communityCountry');
            const lang = document.getElementById('communityLanguage');
            if (ctry) ctry.value = window.userCountryId || '';
            if (lang) lang.value = window.userLanguageId || '';
        }, 100);
    };

    window.addCommunity2Buwana = function(event) {
        event.preventDefault();
        const form = document.getElementById('addCommunityForm');
        const formData = new FormData(form);

        fetch('https://buwana.ecobricks.org/api/add_community.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                closeInfoModal();
                const communityInput = document.getElementById('community_search') || document.getElementById('community_name');
                const communityIdInput = document.getElementById('community_id');
                if (communityInput) communityInput.value = data.community_name;
                if (communityIdInput) {
                    fetch('https://buwana.ecobricks.org/api/api/search_communities_by_id.php?query=' + encodeURIComponent(data.community_name))
                        .then(r => r.json())
                        .then(list => {
                            const match = list.find(c => c.com_name === data.community_name);
                            if (match) communityIdInput.value = match.community_id;
                        })
                        .catch(err => console.error('Error fetching community ID:', err));
                }
            }
        })
        .catch(error => {
            alert('Error adding community. Please try again.');
            console.error('Error:', error);
        });
    };
});
