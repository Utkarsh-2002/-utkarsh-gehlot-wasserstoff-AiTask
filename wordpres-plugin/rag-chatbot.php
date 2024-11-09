<?php
/*
Plugin Name: RAG Chatbot
Description: A chatbot utilizing Retrieval-Augmented Generation with Chain of Thought.
Version: 1.0
Author: Your Name
*/

add_action('init', 'rag_chatbot_init');

// Initialize the REST API endpoint
function rag_chatbot_init() {
    add_action('rest_api_init', function () {
        register_rest_route('rag-chatbot/v1', '/query', array(
            'methods' => 'POST',
            'callback' => 'rag_chatbot_query_handler',
            'permission_callback' => '__return_true',
        ));
    });
}

// Handle incoming chat queries
function rag_chatbot_query_handler(WP_REST_Request $request) {
    $user_query = sanitize_text_field($request->get_param('query'));
    
    // Generate embeddings from the user query
    $embeddings = generate_embeddings($user_query);
    
    // Search for relevant documents based on embeddings
    $search_results = search_relevant_documents($user_query);
    
    // Construct the Chain of Thought context
    $cot_context = perform_chain_of_thought($user_query, $search_results);
    
    // Generate an answer using a Hugging Face model with Chain of Thought context
    $generated_response = generate_answer_with_huggingface($cot_context, $user_query);
    
    return new WP_REST_Response(array(
        'response' => $generated_response,
        'embeddings' => $embeddings,
        'search_results' => $search_results
    ), 200);
}

// Generate embeddings for a given text
function generate_embeddings($text) {
    $response = wp_remote_post('http://localhost:5000/generate_embeddings', array(
        'method' => 'POST',
        'body' => json_encode(array('text' => $text)),
        'headers' => array('Content-Type' => 'application/json'),
    ));

    if (is_wp_error($response)) {
        error_log('Error in generating embeddings: ' . $response->get_error_message());
        return [];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return isset($data['embeddings']) ? $data['embeddings'] : [];
}

// Search for relevant documents
function search_relevant_documents($query) {
    $response = wp_remote_post('http://localhost:5000/search', array(
        'method' => 'POST',
        'body' => json_encode(array('query' => $query)),
        'headers' => array('Content-Type' => 'application/json'),
    ));

    if (is_wp_error($response)) {
        error_log('Error in searching for documents: ' . $response->get_error_message());
        return [];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return isset($data['results']) ? $data['results'] : [];
}

// Generate an answer using Hugging Face with Chain of Thought context
function generate_answer_with_huggingface($context, $question) {
    $response = wp_remote_post('http://localhost:5000/generate_answer', array(
        'method' => 'POST',
        'body' => json_encode(array('context' => $context, 'question' => $question)),
        'headers' => array('Content-Type' => 'application/json'),
    ));

    if (is_wp_error($response)) {
        error_log('Error in Hugging Face response: ' . $response->get_error_message());
        return "Sorry, I couldn't generate a response at the moment.";
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return isset($data['answer']) ? $data['answer'] : "Sorry, I couldn't generate a response.";
}

// Perform Chain of Thought processing
function perform_chain_of_thought($user_query, $search_results) {
    // Enhance context with more detailed steps for reasoning
    $cot_steps = "Step 1: Understand the user's query: '$user_query'\n";
    $cot_steps .= "Step 2: Review the relevant documents:\n";
    
    if (empty($search_results)) {
        $cot_steps .= " - No relevant documents found.\n";
    } else {
        foreach ($search_results as $document) {
            $cot_steps .= " - Document: '$document'\n";
        }
    }

    $cot_steps .= "Step 3: Reasoning through the provided context and documents.\n";
    $cot_steps .= "Step 4: Based on the reasoning and information in the documents, I will generate an answer:\n";

    // Construct a clearer thought process for the model
    $cot_steps .= "Step 5: Answer: ";

    return $cot_steps;
}

// Add shortcode to display chatbot interface
add_shortcode('rag_chatbot_interface', 'rag_chatbot_interface');

function rag_chatbot_interface() {
    ob_start();
    ?>
    <div id="chatbot-container" style="border: 1px solid #ccc; padding: 10px; width: 300px;">
        <h3>Chat with Us!</h3>
        <div id="chat-window" style="height: 200px; overflow-y: auto; background: #f9f9f9; padding: 10px; margin-bottom: 10px;"></div>
        <input type="text" id="user-input" placeholder="Type your message..." style="width: 100%; padding: 8px;">
        <button onclick="sendMessage()">Send</button>
    </div>
    <script>
        async function sendMessage() {
            const inputField = document.getElementById('user-input');
            const userMessage = inputField.value.trim();
            if (!userMessage) return;

            const chatWindow = document.getElementById('chat-window');
            chatWindow.innerHTML += '<div><strong>You:</strong> ' + userMessage + '</div>';
            inputField.value = '';

            // Send the user's query to the chatbot backend
            try {
                const response = await fetch('<?php echo esc_url( get_rest_url(null, 'rag-chatbot/v1/query') ); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ query: userMessage })
                });
                const data = await response.json();
                const botMessage = data.response || 'Sorry, I couldn\'t understand that.';
                chatWindow.innerHTML += '<div><strong>Bot:</strong> ' + botMessage + '</div>';
                chatWindow.scrollTop = chatWindow.scrollHeight;
            } catch (error) {
                console.error('Error:', error);
                chatWindow.innerHTML += '<div><strong>Bot:</strong> There was an error processing your request.</div>';
            }
        }
    </script>
    <?php
    return ob_get_clean();
}
?>
