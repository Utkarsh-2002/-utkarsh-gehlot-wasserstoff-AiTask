# RAG Chatbot with Chain of Thought

This project is a WordPress-based chatbot that uses Retrieval-Augmented Generation (RAG) with Chain of Thought reasoning. It retrieves content from a WordPress database, uses embeddings for document retrieval, and generates responses using a transformer-based model. The chatbot is designed to answer user queries by retrieving relevant content and applying Chain of Thought reasoning to enhance response accuracy.

## Table of Contents
- [Features](#features)
- [Technologies](#technologies)
- [Setup](#setup)
- [Usage](#usage)
- [License](#license)

## Features
- **Embedding Server**: Uses FAISS for efficient similarity search and Sentence Transformers for embeddings.
- **Question Answering**: Uses Hugging Face's `distilbert-base-uncased-distilled-squad` model for answering questions.
- **Chain of Thought Reasoning**: Generates more accurate responses by reasoning through steps.
- **WordPress Integration**: Retrieves posts from a WordPress database to generate document embeddings.
- **REST API**: Custom REST API endpoints for integrating with WordPress and handling chat queries.

## Technologies
- [Python 3.8+](https://www.python.org/) - Backend API
- [FAISS](https://github.com/facebookresearch/faiss) - Fast similarity search
- [Flask](https://flask.palletsprojects.com/) - Web framework for the API
- [Sentence Transformers](https://www.sbert.net/) - Embeddings for text
- [Hugging Face Transformers](https://huggingface.co/transformers/) - Question answering model
- [BeautifulSoup](https://www.crummy.com/software/BeautifulSoup/) - HTML content cleaning
- [MySQL](https://www.mysql.com/) - WordPress database

## Setup

### Prerequisites
- Python 3.8+
- MySQL database with a WordPress installation
- Required Python packages:
  - `faiss`, `numpy`, `flask`, `flask_cors`, `sentence_transformers`, `transformers`, `mysql-connector-python`, `beautifulsoup4`
  
### Instructions
1. **Clone the Repository**
   ```bash
   git clone https://github.com/your-username/rag-chatbot.git
   cd rag-chatbot
Install Dependencies

bash
Copy code
pip install -r requirements.txt
Configure MySQL Database

Update the MySQL connection details in embedding_server.py:
python
Copy code
connection = mysql.connector.connect(
    host="localhost",
    user="root",
    password="your_password",
    database="wordpress_db"
)
Start the Embedding Server

bash
Copy code
python embedding_server.py
Install WordPress Plugin

Copy rag-chatbot.php to the wp-content/plugins/ directory of your WordPress installation.
Activate the plugin from the WordPress admin dashboard.


Usage
Open your WordPress site where the chatbot is embedded via the shortcode [rag_chatbot_interface].
Type in your query, and the chatbot will provide responses by retrieving and reasoning over relevant documents from your WordPress database.
