from flask import Flask, request, jsonify
import pandas as pd
from sklearn.cluster import DBSCAN
from sklearn.preprocessing import StandardScaler

app = Flask(__name__)

@app.route("/")
def home():
    return "Flask AI module is running! Use POST /cluster"

PUROK_COORDS = {
    "Purok 1": (14.900031, 120.523160),
    "Purok 2": (14.904618, 120.510837),
    "Purok 3": (14.898851, 120.516532),
    "Purok 4": (14.904298, 120.509210),
    "Purok 5": (14.8963136, 120.5095971),
}

@app.route("/cluster", methods=["POST"])
def cluster_data():
    data = request.get_json()
    df = pd.DataFrame(data)

    # Rename for consistency
    if "purokName" in df.columns:
        df.rename(columns={"purokName": "purok"}, inplace=True)
    elif "purok_name" in df.columns:
        df.rename(columns={"purok_name": "purok"}, inplace=True)

    # Add lat/lon
    df["lat"] = df["purok"].map(lambda p: PUROK_COORDS.get(p, (0, 0))[0])
    df["lon"] = df["purok"].map(lambda p: PUROK_COORDS.get(p, (0, 0))[1])

    # Select features
    features = df[["dengue", "measles", "flu", "allergies", "diarrhea", "lat", "lon"]]

    # Scale (normalize)
    scaler = StandardScaler()
    features_scaled = scaler.fit_transform(features)

    # Run DBSCAN
    dbscan = DBSCAN(eps=2.5, min_samples=1)
    df["cluster"] = dbscan.fit_predict(features_scaled)

    result = df[["purok", "cluster"]].to_dict(orient="records")
    return jsonify(result)

if __name__ == "__main__":
    app.run(debug=True)
