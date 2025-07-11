<?php
require_once '../earthenAuth_helper.php'; // Include the authentication helper functions
require_once '../auth/session_start.php';

// Set up page variables
$lang = basename(dirname($_SERVER['SCRIPT_NAME']));
$version = '0.15';
$page = 'messenger';
$lastModified = date("Y-m-d\TH:i:s\Z", filemtime(__FILE__));


// Check if user is logged in and session active
if ($is_logged_in) {
    $buwana_id = $_SESSION['buwana_id'] ?? ''; // Retrieve buwana_id from session

    // Include database connections
    require_once '../gobrikconn_env.php';
    require_once '../buwanaconn_env.php';

    // Fetch the user's location data
    $user_continent_icon = getUserContinent($buwana_conn, $buwana_id);
$earthling_emoji = getUserEarthlingEmoji($buwana_conn, $buwana_id);
    $user_location_watershed = getWatershedName($buwana_conn, $buwana_id);
    $user_location_full = getUserFullLocation($buwana_conn, $buwana_id);
    $gea_status = getGEA_status($buwana_id);
    $user_community_name = getCommunityName($buwana_conn, $buwana_id);
    $first_name = getFirstName($buwana_conn, $buwana_id);

    // Run messenger code here

    // Close the database connections
    $buwana_conn->close();
    $gobrik_conn->close();
} else {
    // Redirect to login page with the redirect parameter set to the current page
    echo '<script>
        alert("Please login before viewing this page.");
        window.location.href = "login.php?redirect=' . urlencode($page) . '.php";
    </script>';
    exit();
}

// Output the HTML structure
echo '<!DOCTYPE html>
<html lang="' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">
<head>
<meta charset="UTF-8">
';
?>



<!--
GoBrik.com site version 3.0
Developed and made open source by the Global Ecobrick Alliance
See our git hub repository for the full code and to help out:
https://github.com/gea-ecobricks/gobrik-3.0/tree/main/en-->

<?php require_once("../includes/messenger-inc.php"); ?>





<!-- MESSENGER CONTENT
<div id="top-page-image" class="message-birded top-page-image"></div>-->
<div id="form-submission-box" style="height:fit-content;margin-top: 90px;">
    <div class="form-container" id="messenger-form-container">


     <!--
     <div id="greeting" style="text-align:center;width:100%;margin:auto;">
            <h2 id="greeting">GoBrik Messenger</h2>
            <p id="subgreeting">Welcome to your conversations <?php echo $first_name; ?>.</p>
        </div>
    ACTIVE MESSENGER PHP AND HTML GOES HERE-->

  <div class="messenger-container">
      <button id="mobileBackToConvos" class="toggle-drawer-button hidden" title="Toggle Drawer">‹</button>
    <div class="conversation-list-container">

        <!-- Container for the start conversation button and search box -->
        <div class="start-conversation-container">
            <div style="display:flex;flex-flow:row">
                <button id="startConversationButton" class="start-convo-button">
                    <img src="../svgs/gobrik-3-emblem-tight.svg?v=5" alt="GoBrik Emblem" class="button-icon">
                    <span style="margin:auto auto auto 0px;">New Chat...</span>
                </button>
                <button id="toggleConvoDrawer" class="toggle-drawer-button" title="Toggle Drawer">‹</button>

            </div>

            <div id="searchBoxContainer" class="hidden" style="position: relative;">
                <button id="clearSearchButton" class="clear-search-button" aria-label="Clear Search"></button>

                <input type="text" id="userSearchInput" placeholder="Search users..." />
                <div class="spinner-right" id="userSearchSpinner"></div>
                <div id="searchResults"></div>
                <div id="selectedUsers">
                    <!-- Selected users will appear here -->
                </div>
                <button id="createConversationButton" class="create-button">Create Conversation →</button>
            </div>

        </div>


        <!-- Scrollable container for conversations -->
        <div class="conversation-list" id="conversation-list">
            <!-- Conversations will be dynamically loaded here -->
        </div>
    </div>

    <div class="message-thread" id="message-thread">
        <div id="message-list">

            <div id="messenger-welcome" class="full-convo-message">
                <div class="message-birded" style="width:300px; height:140px;"></div>
                <h4>Welcome to GoBrik messenger.</h4>
                <p style="font-size:1em; margin-top: -20px;" id="convo-and-messages-status">
                    You have $number_conversations and $unread_messages totalling $total_mbs_on_server.  Choose a conversation or start a new one!</p>
            <h4 style="margin-top: -5px;">👈</h4>
        </div>


            <!-- Messages will be dynamically loaded here -->
        </div>
        <div class="message-input-wrapper" style="position: relative; padding: 10px 10px 15px 10px;display:flex;">
            <button type="button" id="uploadPhotoButton" class="upload-photo-button" title="Upload Photo" aria-label="Upload Photo">📎</button>
            <textarea id="messageInput" placeholder="Your message..." rows="1"></textarea>
            <input type="file" id="imageUploadInput" accept="image/jpeg, image/jpg, image/png, image/webp" style="display: none;" />
            <button id="sendButton" title="Send" aria-label="Send" class="send-message-button"></button>
            <div id="uploadSpinner" class="upload-spinner hidden"></div>
            <div id="errorIndicator" class="error-indicator hidden">⚠️</div>
            <span id="imageFileName" class="image-file-name"></span>
        </div>
    </div>
</div>



    </div>
</div>

</div><!--closes main and starry background-->

<!-- NO FOOTER STARTS HERE -->


<script>
    // SETUP PAGE
    $(document).ready(function() {
        function setUpMessengerWindow() {
            document.getElementById("header").style.height = "60px";
            document.getElementById("gea-logo").style.width = "170px";
            document.getElementById("gea-logo").style.height = "35px";
            document.getElementById("logo-gobrik").style.opacity = "0.9";
            document.getElementById("settings-buttons").style.padding = "12px 43px 10px 12px";
            document.getElementById("language-menu-slider").style.top = "-35px";
            document.getElementById("login-menu-slider").style.top = "-35px";
            document.getElementById("form-submission-box").style.marginTop = "73px";
//             document.getElementById('page-content').classList.add('modal-open');
//             document.documentElement.classList.add('modal-locked');
//             document.body.classList.add('modal-locked');
        }

        // Call the function when the document is ready
        setUpMessengerWindow();
        showMessagingCounts()

       });

 </script>

<script>

    // SECTION 1: Define Global Variables
    var userId = '<?php echo $buwana_id; ?>'; // Get the user's ID from PHP


    //Get the convo and message stats:

    function showMessagingCounts() {
    $.ajax({
        url: '../messenger/check_message_stats.php',
        method: 'GET',
        data: { user_id: userId }, // Assuming `userId` is already defined globally
        success: function(response) {
            if (response.status === 'success') {
                // Extract stats from the response
                const numberOfConversations = response.number_conversations;
                const unreadMessages = response.unread_messages;
                const totalMbsOnServer = response.total_mbs_on_server;

                // Update the div with the retrieved stats
                $('#convo-and-messages-status').html(
                    `You have ${numberOfConversations} conversations and ${unreadMessages} unread messages totalling ${totalMbsOnServer} MB. Choose a conversation or start a new one!`
                );
            } else {
                console.error('Error retrieving message stats:', response.message);
            }
        },
        error: function(error) {
            console.error('Error in AJAX request:', error);
        }
    });
}


     // SECTION 2: Load Conversations
   function loadConversations() {
    $.ajax({
        url: '../messenger/get_conversations.php',
        method: 'GET',
        data: { user_id: userId },
        success: function(response) {
            console.log('Response from get_conversations.php:', response);
            if (response.status === 'success') {
                const serverTime = response.server_time; // Assuming `server_time` is returned by get_conversations.php
                renderConversations(response.conversations, serverTime);
            } else {
                alert(response.message);
            }
        },
        error: function(error) {
            console.error('Error fetching conversations:', error);
        }
    });
}


    // 2.5 Function to show messages in full width on mobile
  // 2.5 Function to show messages in full width on mobile
function showMessagesOnMobile() {
    // On mobile, expand to full width for the message thread, hide the drawer and the startConversationButton
    $('#start-conversation-container').addClass('hidden');
    $('.conversation-list-container').css({
        'width': '0',
        'display': 'none'
    });
    $('.message-thread').css({
        'width': '100%',
        'display': 'flex'
    });
    $('#mobileBackToConvos').removeClass('hidden');
}

// Global flag to track if it's the first render of a conversation
let isFirstRender = true;

function renderConversations(conversations, serverTime) {
    const conversationList = $('#conversation-list');
    conversationList.empty();

    conversations.forEach((conv) => {
        // Format the timestamp using the helper function and serverTime
        const formattedTimestamp = formatTimestamp(conv.updated_at, serverTime);

        // Set up the size in bytes and new message indicator based on all_msgs_posted
        const sizeInBytes = conv.size_in_bytes || 0; // Default to 0 if undefined
        const newMessageIndicator = conv.all_msgs_posted === 0
            ? '<span title="new message" id="new-message">| 📫</span>'
            : ''; // Only show if all_msgs_posted is 0

        // Use a default message if there is no last message
        const lastMessage = conv.last_message ? conv.last_message : "🥚 No messages yet.";
        const trimmedMessage = lastMessage.length > 50
            ? lastMessage.substring(0, 50) + '...'
            : lastMessage;

        // Build the conversation HTML with timestamp, size, and new message indicator
        const convElement = `
            <div class="conversation-item" data-conversation-id="${conv.conversation_id}" data-all-msgs-posted="${conv.all_msgs_posted}">
                <div class="delete-conversation">×</div>
                <div class="conversation-icon" id="conversation-icon-${conv.conversation_id}">
                    <span class="initial">${conv.other_participants.charAt(0)}</span>
                </div>
                <div class="conversation-details">
                    <p><strong>${conv.other_participants}</strong></p>
                    <p class="convo-preview-text">${trimmedMessage}</p>
                    <p class="timestamp">${formattedTimestamp} | ${sizeInBytes} bytes ${newMessageIndicator}</p>
                </div>
            </div>
        `;

        conversationList.append(convElement);
    });

    // Add click event to each conversation
    $('.conversation-item').on('click', function() {
        const conversationId = $(this).data('conversation-id');
        const allMsgsPosted = $(this).data('all-msgs-posted');

        // Show alert to confirm the value of all_msgs_posted
//        alert(`Conversation ID: ${conversationId}\nAll Messages Posted: ${allMsgsPosted}`);
//

        // Trigger mobile view if the viewport is below 769px
        if (window.innerWidth < 769) {
            showMessagesOnMobile();
        }

        loadMessages(conversationId, allMsgsPosted);
        $('.conversation-item').removeClass('active');
        $(this).addClass('active');
    });
}


// Helper function to format the timestamp with adjusted server time
function formatTimestamp(timestamp, serverTime) {
    // Ensure timestamps are treated as UTC by appending 'Z'
    const messageDate = new Date(`${timestamp.replace(' ', 'T')}Z`);
    let serverDate = new Date(`${serverTime.replace(' ', 'T')}Z`);

    // Adjust serverDate to UTC+8 (add 8 hours)
    serverDate.setHours(serverDate.getHours() + 8);

    // Calculate the difference in seconds between adjusted server time and message time
    const diffInSeconds = Math.floor((serverDate - messageDate) / 1000);

    console.log(`Timestamp: ${timestamp}, Adjusted Server Time: ${serverDate.toISOString()}, Diff in seconds: ${diffInSeconds}`);

    // Format the relative time based on the difference
    if (diffInSeconds < 30) {
        return "A moment ago";
    } else if (diffInSeconds < 180) {
        return "A minute ago";
    } else if (diffInSeconds < 86400 && serverDate.toDateString() === messageDate.toDateString()) {
        return `Today at ${messageDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
    } else if (diffInSeconds < 172800 && serverDate.toDateString() !== messageDate.toDateString()) {
        return `Yesterday at ${messageDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
    } else if (serverDate.getFullYear() === messageDate.getFullYear()) {
        return messageDate.toLocaleDateString([], { month: 'short', day: 'numeric' });
    } else {
        return messageDate.toLocaleDateString([], { year: 'numeric', month: 'short', day: 'numeric' });
    }
}

// SECTION 3: Load messages using AJAX to fetch from the database using get_messages.php.
// Then, Render Messages is called, and conversations are updated.


function loadMessages(conversationId, allMsgsPosted) {
    $.ajax({
        url: '../messenger/get_messages.php',
        method: 'GET',
        data: { conversation_id: conversationId, user_id: userId },
        success: function(response) {
            if (response.status === 'success') {
                const messages = response.messages;
                const firstName = response.first_name; // Retrieve first name
                const otherParticipants = response.other_participants; // Retrieve other participants
                const serverTime = response.server_time; // Retrieve server time for consistent display

                // Pass conversationId and serverTime to renderMessages
                renderMessages(messages, conversationId, serverTime);

                // Check if the conversation is empty and display the "New Chat" message if needed
                if (messages.length === 0) {
                    showNewChatMessage(firstName, otherParticipants);
                } else {
                    hideNewChatMessage();

                    // Update conversation details with the latest message and timestamp
                    const lastMessage = messages[messages.length - 1];
                    updateConversationDetails(conversationId, lastMessage);
                }
            } else {
                alert(response.message);
            }
        },
        error: function(error) {
            console.error('Error fetching messages:', error);
        },
        complete: function() {
            // Explicitly set allMsgsPosted to 1 before passing it
            allMsgsPosted = 1;

            console.log('Refreshing messages with:', { conversationId, allMsgsPosted });

            // Schedule the next message load after 5 seconds
//             setTimeout(function() {
//                 refreshMessages(conversationId, allMsgsPosted);
//             }, 5000);
        }
    });
}



function refreshMessages(conversationId, allMsgsPosted) {
    $.ajax({
        url: '../messenger/get_messages.php',
        method: 'GET',
        data: { conversation_id: conversationId, user_id: userId },
        success: function(response) {
            if (response.status === 'success') {
                const messages = response.messages;
                const firstName = response.first_name; // Retrieve first name
                const otherParticipants = response.other_participants; // Retrieve other participants
                const serverTime = response.server_time; // Retrieve server time for consistent display
                const updatedAllMsgsPosted = response.all_msgs_posted; // Get the fresh value from response

                // Only render messages if it’s the first render or updated all_msgs_posted is 0
                if (isFirstRender || updatedAllMsgsPosted === 0) {
                    if (messages.length > 0) {
                        renderMessages(messages, conversationId, serverTime);
                    }

                    // Check if the conversation is empty and display the "New Chat" message if needed
                    if (messages.length === 0) {
                        showNewChatMessage(firstName, otherParticipants);
                    } else {
                        hideNewChatMessage();

                        // Update conversation details with the latest message and timestamp
                        const lastMessage = messages[messages.length - 1];
                        updateConversationDetails(conversationId, lastMessage);
                    }
                }

                // Update allMsgsPosted with the latest value from the database
                allMsgsPosted = updatedAllMsgsPosted;

                // Reset first render flag after the initial load
                isFirstRender = false;
            } else {
                console.error('Error in response:', response.message);
            }
        },
        error: function(error) {
            console.error('Error fetching messages:', error);
        },
        complete: function() {
            // Schedule the next message load after 4 seconds
            setTimeout(function() {
                refreshMessages(conversationId, allMsgsPosted);
            }, 5000);
        }
    });
}





    // Function to update conversation details
    function updateConversationDetails(conversationId, lastMessage) {
        const lastMessageText = lastMessage.content.length > 50
            ? lastMessage.content.substring(0, 50) + '...'
            : lastMessage.content;
        const updatedAt = lastMessage.created_at;
        const otherParticipants = lastMessage.sender_name;

        // Find the conversation element by its data attribute
        const conversationElement = $(`.conversation-item[data-conversation-id="${conversationId}"]`);

        // Update the details inside the conversation element
        conversationElement.find('.convo-preview-text').text(lastMessageText);
        conversationElement.find('.timestamp').text(updatedAt);
        conversationElement.find('strong').text(otherParticipants);

        // Optionally, move the updated conversation to the top of the list to reflect recent activity
        $('#conversation-list').prepend(conversationElement);
    }



   // Function to show the "New Chat" message
function showNewChatMessage(first_name, participants) {
    const newChatMessage = `
        <div id="no-messages-yet" class="full-convo-message">
            <h1>🐣</h1>
            <h4>This chat with ${first_name} and ${participants} is just getting going.</h4>
            <p style="font-size:1em; margin-top: -20px;">Send a message to get cracking!</p>
            <h4>👇</h4>
        </div>
    `;
    $('#message-list').html(newChatMessage); // Insert the message into the message list
}


    // Function to hide the "New Chat" message
    function hideNewChatMessage() {
        $('#no-messages-yet').remove(); // Remove the new chat message if it exists
    }



function renderMessages(messages, conversation_id, serverTime) {
    const messageList = $('#message-list');
    messageList.empty();

    let lastMessageId = null;
    let isLastMessageFromUser = false;

    messages.forEach((msg) => {
        const isUserMessage = msg.sender_id == userId;
        const messageClass = isUserMessage ? 'self' : '';
        const thumbnailHtml = msg.thumbnail_url
            ? `<a href="#" class="thumbnail-link" data-full-url="../${msg.image_url}">
                <img src="../${msg.thumbnail_url}" alt="Image attachment" class="message-thumbnail" />
               </a>`
            : '';

        // Use serverTime in UTC for consistent comparisons
        const formattedTimestamp = formatTimestamp(msg.created_at, serverTime);

        const messageStatus = isUserMessage ? msg.status_sender : msg.status_reader;

        const msgElement = $(`
            <div class="message-item ${messageClass}" data-message-id="${msg.message_id}" data-sender-id="${msg.sender_id}">
                ${thumbnailHtml}
                <p class="sender">${msg.sender_name}</p>
                <p class="the-message-text">${msg.content}</p>
                <p class="timestamp">${formattedTimestamp} | <span class="message-status">${messageStatus}</span></p>
            </div>
        `);

        messageList.append(msgElement);

        lastMessageId = msg.message_id;
        isLastMessageFromUser = isUserMessage;
    });

    if (lastMessageId && isLastMessageFromUser) {
        $(`.message-item[data-message-id="${lastMessageId}"]`).addClass('flash');
        setTimeout(() => {
            $(`.message-item[data-message-id="${lastMessageId}"]`).removeClass('flash');
        }, 1500);
    }

    messageList.scrollTop(messageList.prop("scrollHeight"));

    setTimeout(() => {
        updateMessageStatuses(conversation_id);
    }, 500);

    // Add click event to open modal for each thumbnail link
    $('.thumbnail-link').on('click', function(event) {
        event.preventDefault();
        const fullUrl = $(this).data('full-url');
        openPhotoModal(fullUrl);
    });

    // Delay calling updateMessageStatuses by 500ms after rendering is complete
    setTimeout(() => {
        updateMessageStatuses(conversation_id);
    }, 500);
}

function updateMessageStatuses(conversation_id) {
    const messagesToUpdate = [];

    $('#message-list .message-item').each(function() {
        const msgSenderId = parseInt($(this).data('sender-id'), 10);
        const msgStatus = $(this).find('.message-status').text().trim();
        const messageId = $(this).data('message-id');
        const currentUserId = parseInt(userId, 10);

        // Messages sent by the user that are still "Sending..."
        if (msgSenderId === currentUserId && msgStatus === 'Sending...') {
            messagesToUpdate.push({ messageId, newStatus: 'sent', updateField: 'status_sender' });
            $(this).find('.message-status').text('Sent');
        }

        // Messages read by someone other than the sender
        else if (msgSenderId !== currentUserId && msgStatus === 'Unread') {
            messagesToUpdate.push({ messageId, newStatus: 'read', updateField: 'status_sender' });
            messagesToUpdate.push({ messageId, newStatus: 'received', updateField: 'status_reader' });
            $(this).find('.message-status').text('Received');
        }
    });

    // Process updates sequentially if there are messages to update
    if (messagesToUpdate.length > 0) {
        updateMessagesSequentially(messagesToUpdate);
    }
}

// Function to handle sequential updating of message statuses
function updateMessagesSequentially(messagesToUpdate) {
    if (messagesToUpdate.length === 0) return;

    const { messageId, newStatus, updateField } = messagesToUpdate.shift();

    // AJAX call to update message status on the server
    $.ajax({
        url: '../messenger/update_message_status.php',
        method: 'POST',
        data: { message_id: messageId, user_id: userId, status: newStatus, field: updateField },
        success: function(response) {
            if (response.status === 'success') {
                console.log(`Message ID ${messageId} updated to '${newStatus}' successfully.`);
                // Continue updating the next message in the list
                updateMessagesSequentially(messagesToUpdate);
            } else {
                console.error('Error updating message status:', response.message || 'Unknown error');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error in AJAX request to update message status:', textStatus, errorThrown);
        }
    });
}




















$(document).ready(function() {
    const selectedUsers = new Set();

    // Show search box when "Start Conversation" button is clicked and hide the button
    $('#startConversationButton').on('click', function() {
        $(this).addClass('hidden'); // Hide the start conversation button
        $('#searchBoxContainer').removeClass('hidden'); // Show the search box
        $('#userSearchInput').focus();
        $('#toggleConvoDrawer').addClass('hidden');
        $('#clearSearchButton').show(); // Show the clear search button
        toggleCreateButton(); // Ensure the create button state is correct when search starts
    });

    // Handle user search input
    $('#userSearchInput').on('input', function() {
        const query = $(this).val().trim();
        if (query.length >= 3) { // Adjusted to trigger after 3 characters
            searchUsers(query);
        } else {
            $('#searchResults').empty();
        }
    });

    // Handle clear search button click
    $('#clearSearchButton').on('click', function() {
        $('#userSearchInput').val(''); // Clear the input field
        $('#searchResults').empty(); // Clear the search results
        $('#searchBoxContainer').addClass('hidden'); // Hide the search box
        $('#startConversationButton').removeClass('hidden'); // Show the start conversation button
        $('#toggleConvoDrawer').removeClass('hidden'); // Show the toggle button again
        $(this).hide(); // Hide the clear search button
        selectedUsers.clear(); // Clear selected users
        $('#selectedUsers').empty(); // Clear displayed selected users
        toggleCreateButton(); // Ensure the create button is disabled when clearing the search
    });

    // Show the clear button when there's text in the search input
    $('#userSearchInput').on('input', function() {
        if ($(this).val().trim() !== '') {
            $('#clearSearchButton').show();
        } else {
            $('#clearSearchButton').hide();
        }
    });

    // Function to search for users
    function searchUsers(query) {
        $('#userSearchSpinner').show(); // Show the spinner before starting the AJAX request

        $.ajax({
            url: '../messenger/search_users.php',
            method: 'GET',
            data: {
                query: query,
                user_id: userId // Ensure userId is available and passed in the request
            },
            success: function(response) {
                $('#userSearchSpinner').hide(); // Hide the spinner after receiving the response
                if (response.status === 'success') {
                    renderSearchResults(response.users);
                } else {
                    $('#searchResults').html('<p>No users found</p>');
                }
            },
            error: function(error) {
                $('#userSearchSpinner').hide(); // Hide the spinner if there's an error
                console.error('Error searching users:', error);
                $('#searchResults').html('<p>An error occurred while searching.</p>');
            }
        });
    }

    // Render search results as a dropdown list
    function renderSearchResults(users) {
        const searchResults = $('#searchResults');
        searchResults.empty();
        if (users.length > 0) {
            users.forEach(user => {
                if (!selectedUsers.has(user.buwana_id)) {
                    const userElement = `<div class="search-result-item" data-user-id="${user.buwana_id}">${user.first_name} ${user.last_name}</div>`;
                    searchResults.append(userElement);
                }
            });

            // Add click event for each search result item
            $('.search-result-item').on('click', function() {
                const userId = $(this).data('user-id');
                const userName = $(this).text();
                if (selectedUsers.size < 5) {
                    selectedUsers.add(userId);
                    $('#selectedUsers').append(`<div class="selected-user-item" data-user-id="${userId}">+ ${userName}</div>`);
                    $(this).remove(); // Remove from search results
                    $('#userSearchInput').val(''); // Clear the search input box to reset the dropdown
                    $('#searchResults').empty(); // Clear the search results
                    toggleCreateButton(); // Enable or disable the create button based on selection
                }
            });
        } else {
            searchResults.html('<p>No users found</p>');
        }
    }

    // Function to enable or disable the create conversation button based on selection
    function toggleCreateButton() {
        if (selectedUsers.size > 0) {
            $('#createConversationButton').prop('disabled', false).removeClass('hidden'); // Enable the create button if users are selected
        } else {
            $('#createConversationButton').prop('disabled', true).addClass('hidden'); // Disable the create button if no users are selected
        }
    }



    // Function for creating a new conversation
function createConversation() {
    const participantIds = Array.from(selectedUsers);
    $.ajax({
        url: '../messenger/create_conversation.php',
        method: 'POST',
        data: {
            created_by: userId,
            participant_ids: JSON.stringify(participantIds)
        },
        success: function(response) {
            console.log('Response from create_conversation.php:', response);
            if (response.status === 'success') {
                const conversationId = response.conversation_id; // Extract the conversation ID from the response

                // Hide the search box and reset the interface
                $('#searchBoxContainer').addClass('hidden');
                $('#startConversationButton').removeClass('hidden');
                $('#userSearchInput').val('');
                $('#searchResults').empty();
                $('#selectedUsers').empty();
                $('#toggleConvoDrawer').removeClass('hidden');
                selectedUsers.clear();

                // Refresh the conversations list
                loadConversations();


            } else {
                alert(response.message);
            }
        },
        error: function(error) {
            console.error('Error creating conversation:', error);
        }
    });
}



        // Handle the create conversation button click
        $('#createConversationButton').on('click', createConversation);

        // Remove a selected user when clicked
        $('#selectedUsers').on('click', '.selected-user-item', function() {
            const userId = $(this).data('user-id');
            selectedUsers.delete(userId);
            $(this).remove();
            if (selectedUsers.size === 0) {
                $('#createConversationButton').prop('disabled', true);
            }
        });

        // Load conversations on page load
        loadConversations();
    });

    // SECTION 5: JavaScript/jQuery for Sending Messages
$(document).ready(function() {
    const maxFileSize = 10 * 1024 * 1024; // 10 MB
    const userId = '<?php echo $buwana_id; ?>'; // Get the user's ID from PHP

    // Function to show the spinner
    function showUploadSpinner() {
        $('#sendButton').hide();
        $('#errorIndicator').hide();
        $('#uploadSpinner').show();
    }

    // Function to hide the spinner and show the send button
    function hideUploadSpinner() {
        $('#uploadSpinner').hide();
        $('#sendButton').show();
    }

    // Function to show the error indicator
    function showErrorIndicator() {
        $('#uploadSpinner').hide();
        $('#sendButton').hide();
        $('#errorIndicator').show();
    }

    // Handle send button click for messages
    $('#sendButton').on('click', function() {
        const messageContent = $('#messageInput').val().trim();
        const selectedConversationId = $('.conversation-item.active').data('conversation-id');
        const file = $('#imageUploadInput')[0].files[0];

        // Check if a conversation is selected
        if (selectedConversationId && (messageContent || file)) {
            const formData = new FormData();
            formData.append('conversation_id', selectedConversationId);
            formData.append('sender_id', userId);
            formData.append('content', messageContent);

            // If a valid file is selected, append it to the FormData
            if (file && validateFile(file)) {
                formData.append('image', file);
                showUploadSpinner();
            }

            // Submit the message along with any attached image
            $.ajax({
                url: '../messenger/send_message.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('Response from send_message.php:', response);
                    if (response.status === 'success') {
                        $('#messageInput').val(''); // Clear the input field
                        loadMessages(selectedConversationId); // Refresh message list
                        hideUploadSpinner(); // Hide spinner on success
                        resetUploadButton(); // Reset upload button if image was attached
                    } else {
                        hideUploadSpinner();
                        showErrorIndicator(); // Show error indicator if there's an issue
                        setTimeout(hideErrorIndicator, 3000); // Hide the error after 3 seconds
                    }
                },
                error: function(error) {
                    console.error('Error sending message:', error);
                    hideUploadSpinner();
                    showErrorIndicator();
                    setTimeout(hideErrorIndicator, 3000); // Hide the error after 3 seconds
                }
            });
        } else {
            alert('Please select a conversation and enter a message or attach an image.');
        }
    });

    // Function to validate the file type and size
    function validateFile(file) {
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        return validTypes.includes(file.type) && file.size <= maxFileSize;
    }

    // Function to hide the error indicator and show the send button
    function hideErrorIndicator() {
        $('#errorIndicator').hide();
        $('#sendButton').show();
    }
});



</script>

<script>
    $(document).ready(function() {
    // Add click event to the delete button inside each conversation item
    $(document).on('click', '.delete-conversation', function(event) {
        event.stopPropagation(); // Prevent triggering the conversation click event

        // Get the conversation ID from the parent .conversation-item
        const conversationId = $(this).closest('.conversation-item').data('conversation-id');

        // Confirm with the user before proceeding
        const confirmation = confirm("Are you sure you want to delete this conversation? Everyone's messages will be deleted permanently.");
        if (confirmation) {
            deleteConversation(conversationId);
        }
    });

    // Function to delete the conversation
    function deleteConversation(conversationId) {
        $.ajax({
            url: '../messenger/delete_conversation.php', // Endpoint to handle conversation deletion
            method: 'POST',
            data: {
                conversation_id: conversationId
            },
            success: function(response) {
                if (response.status === 'success') {
                    alert('Conversation deleted successfully.');
                    loadConversations(); // Refresh the conversation list after deletion
                } else {
                    alert('Failed to delete the conversation. Please try again.');
                }
            },
            error: function(error) {
                console.error('Error deleting conversation:', error);
                alert('An error occurred while deleting the conversation. Please try again.');
            }
        });
    }
});
</script>




<script>

    $(document).ready(function() {
    // Listen for keypress event on the textarea
    $('#messageInput').on('keypress', function(event) {
        // Check if the key pressed is "Enter" (key code 13) and if there is text in the input
        if (event.which === 13 && !event.shiftKey) {
            event.preventDefault(); // Prevent the default behavior of adding a new line
            const messageContent = $(this).val().trim();

            // If the message content is not empty, trigger the send button click
            if (messageContent) {
                $('#sendButton').click();
            }
        }
    });
});

</script>

<script>

$(document).ready(function() {
    let isDrawerCollapsed = false;
//
//     // Adjust the initial state based on screen width
//     function adjustDrawerState() {
//         if ($(window).width() > 800) {
//             $('.conversation-list-container').css('width', '30%');
//             $('.message-thread').show();
//             $('#startConversationButton').removeClass('hidden');
//             $('#toggleConvoDrawer').html('‹'); // Button indicates collapse
//             isDrawerCollapsed = false;
//         } else {
//             // For screens smaller than 800px, collapse the drawer initially
//             $('.conversation-list-container').css('width', '100%');
//             $('.message-thread').hide();
//             $('#toggleConvoDrawer').html('›'); // Button indicates expand
//             isDrawerCollapsed = true;
//         }
//     }
//
//     // Adjust drawer state when the window is resized
//     $(window).on('resize', function() {
//         adjustDrawerState();
//     });
//
//     // Initial setup based on window size
//     adjustDrawerState();

    // Toggle drawer when the button is clicked
    $('#toggleConvoDrawer').on('click', function() {
        if ($(window).width() > 800) {
            if (isDrawerCollapsed) {
                // Expand the drawer to 30% width
                $('.conversation-list-container').css('width', '30%');
                $('.message-thread').css('width', '70%').show();
                $('#toggleConvoDrawer').html('‹'); // Button indicates collapse
                $('.conversation-item').removeClass('collapsed').addClass('expanded');
                $('.delete-conversation').removeClass('hidden');
                $('#startConversationButton').removeClass('hidden');
            } else {
                // Collapse to minimal view
                $('.delete-conversation').addClass('hidden');
                $('.conversation-list-container').css('width', '80px');
                $('.message-thread').css('width', 'calc(100% - 60px)').show();
                $('#toggleConvoDrawer').html('›'); // Button indicates expand
                $('#startConversationButton').addClass('hidden');
                $('.conversation-item').removeClass('expanded').addClass('collapsed');
            }

            isDrawerCollapsed = !isDrawerCollapsed; // Toggle the state
        }
    });

    // Handle the back-to-conversations button for mobile
    $('#mobileBackToConvos').on('click', function() {
        // Show the conversation list and hide the message thread
        $('.conversation-list-container').css('width', '100%');
        $('.message-thread').hide();
        $('#toggleConvoDrawer').addClass('hidden');
        $('#toggleConvoDrawer').html('›'); // Indicate that the drawer can be expanded
        $('.conversation-item').removeClass('collapsed').addClass('expanded');
        isDrawerCollapsed = true; // Set the drawer state to collapsed
         $('.conversation-list-container').css('display', 'flex');
         $('#mobileBackToConvos').addClass('hidden');
    });
});






</script>

<script src="../scripts/messenger.js?v=2.6"></script>




</body>
</html>