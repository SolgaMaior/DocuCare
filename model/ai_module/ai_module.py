from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import numpy as np
from sklearn.preprocessing import StandardScaler
from sklearn.cluster import AgglomerativeClustering

app = Flask(__name__)
CORS(app)


@app.route("/cluster", methods=["POST"])
def cluster_data():
    """
    Perform hierarchical clustering on purok disease data (distance-based).
    Returns cluster assignments, severity levels, and cluster-level disease trends.
    """
    try:
        payload = request.get_json()
        print(payload)
        if not payload:
            return jsonify({"error": "No data provided"}), 400


        if isinstance(payload, list):
            data = payload
            distance_threshold = 3.88  
        elif isinstance(payload, dict):
            data = payload.get("data", [])
            distance_threshold = float(payload.get("distance_threshold", 3.88))
        else:
            return jsonify({"error": "Invalid JSON structure"}), 400

        if not data:
            return jsonify({"error": "No purok data found"}), 400

        df = pd.DataFrame(data)
        print(data)
        # Normalize column names
        df.rename(columns=lambda x: x.lower().strip(), inplace=True)
        if "purokname" in df.columns:
            df.rename(columns={"purokname": "purok"}, inplace=True)
        elif "purok_name" in df.columns:
            df.rename(columns={"purok_name": "purok"}, inplace=True)

        diseases = ["dengue", "measles", "flu", "allergies", "diarrhea"]

        # Ensure all disease columns exist
        for disease in diseases:
            if disease not in df.columns:
                df[disease] = 0

        # Validate purok uniqueness
        if df["purok"].duplicated().any():
            return jsonify({"error": "Duplicate purok entries found"}), 400

        # Convert numeric columns
        df[diseases] = df[diseases].apply(pd.to_numeric, errors='coerce').fillna(0)
        df["total_cases"] = df[diseases].sum(axis=1)

        # Standardize values
        scaler = StandardScaler()
        disease_scaled = scaler.fit_transform(df[diseases])

        # Distance-based hierarchical clustering
        clustering = AgglomerativeClustering(
            n_clusters=None,
            distance_threshold=distance_threshold,
            linkage="ward"
        )
        df["cluster"] = clustering.fit_predict(disease_scaled)

        # Analyze clusters
        cluster_info = {}
        for cluster_id in sorted(df["cluster"].unique()):
            cluster_subset = df[df["cluster"] == cluster_id]
            avg_cases = cluster_subset[diseases].mean()

            # Identify dominant diseases (>= 60th percentile)
            threshold = np.percentile(avg_cases, 60)
            dominant_diseases = {
                disease: round(float(value), 2)
                for disease, value in avg_cases.items()
                if value >= threshold
            }

            cluster_info[int(cluster_id)] = {
                "puroks": cluster_subset["purok"].tolist(),
                "dominant_diseases": dominant_diseases,
                "avg_total": round(float(cluster_subset["total_cases"].mean()), 2),
                "size": int(len(cluster_subset)),
            }

        # Assign severity per purok
        max_cases = df["total_cases"].max()
        df["severity"] = df["total_cases"].apply(
            lambda x: "high" if x > max_cases * 0.6
            else "medium" if x > max_cases * 0.3
            else "low"
        )

        # Identify dominant disease per purok
        df["dominant_disease"] = df[diseases].idxmax(axis=1)
        df["dominant_count"] = df[diseases].max(axis=1)

        # Prepare results
        clusters_result = [
            {
                "purok": row["purok"],
                "cluster": int(row["cluster"]),
                "severity": row["severity"],
                "total_cases": int(row["total_cases"]),
                "dominant_disease": row["dominant_disease"],
                "dominant_count": int(row["dominant_count"]),
                "cases": {d: int(row[d]) for d in diseases},
            }
            for _, row in df.iterrows()
        ]

        summary = {
            "total_puroks": int(len(df)),
            "total_clusters": int(df["cluster"].nunique()),
            "total_cases": int(df["total_cases"].sum()),
            "distance_threshold": distance_threshold
        }

        return jsonify({
            "clusters": clusters_result,
            "cluster_analysis": cluster_info,
            "summary": summary,
        })

    except Exception as e:
        print(f"Clustering error: {e}")
        return jsonify({"error": str(e)}), 500


if __name__ == "__main__":
    app.run(debug=True, host="0.0.0.0", port=5000)
