from flask import Flask, request, jsonify, send_file
import os
import sqlite3
import cv2
import numpy as np
import matplotlib.pyplot as plt
from flask_cors import CORS

app = Flask(__name__)
CORS(app)  # Allows frontend requests

UPLOAD_FOLDER = "uploads"
os.makedirs(UPLOAD_FOLDER, exist_ok=True)
DB_FILE = "disaster_data.db"

# Initialize database
def init_db():
    with sqlite3.connect(DB_FILE) as conn:
        cursor = conn.cursor()
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS image_analysis (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                image_path TEXT NOT NULL,
                severity TEXT NOT NULL,
                uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        """)
        conn.commit()

init_db()

# Function to analyze severity using OpenCV (basic logic for low-end devices)
def analyze_image(image_path):
    image = cv2.imread(image_path, cv2.IMREAD_GRAYSCALE)  # Load image in grayscale
    edges = cv2.Canny(image, 50, 150)  # Edge detection to detect damage
    
    edge_density = np.sum(edges) / (image.shape[0] * image.shape[1])  # Edge density metric

    # Simple threshold-based severity classification
    if edge_density < 0.005:
        return "Low"
    elif edge_density < 0.015:
        return "Moderate"
    else:
        return "High"

# Endpoint to upload an image and process it
@app.route("/upload", methods=["POST"])
def upload_image():
    if "image" not in request.files:
        return jsonify({"error": "No image file provided"}), 400

    image = request.files["image"]
    file_path = os.path.join(UPLOAD_FOLDER, image.filename)
    image.save(file_path)

    severity = analyze_image(file_path)

    with sqlite3.connect(DB_FILE) as conn:
        cursor = conn.cursor()
        cursor.execute("INSERT INTO image_analysis (image_path, severity) VALUES (?, ?)", (file_path, severity))
        conn.commit()

    return jsonify({"message": "Image uploaded and analyzed successfully", "severity": severity})

# Endpoint to fetch severity data for plotting
@app.route("/get_severity_data", methods=["GET"])
def get_severity_data():
    with sqlite3.connect(DB_FILE) as conn:
        cursor = conn.cursor()
        cursor.execute("SELECT severity, COUNT(*) FROM image_analysis GROUP BY severity")
        data = {"Low": 0, "Moderate": 0, "High": 0}
        for severity, count in cursor.fetchall():
            data[severity] = count
    return jsonify(data)

# Endpoint to generate a severity graph
@app.route("/plot_severity", methods=["GET"])
def plot_severity():
    with sqlite3.connect(DB_FILE) as conn:
        cursor = conn.cursor()
        cursor.execute("SELECT severity, COUNT(*) FROM image_analysis GROUP BY severity")
        data = {"Low": 0, "Moderate": 0, "High": 0}
        for severity, count in cursor.fetchall():
            data[severity] = count

    # Create a simple bar chart
    plt.figure(figsize=(5, 3))
    plt.bar(data.keys(), data.values(), color=["green", "orange", "red"])
    plt.xlabel("Severity Level")
    plt.ylabel("Number of Cases")
    plt.title("Disaster Severity Analysis")
    plt.savefig("severity_chart.png")
    plt.close()

    return send_file("severity_chart.png", mimetype="image/png")

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)
