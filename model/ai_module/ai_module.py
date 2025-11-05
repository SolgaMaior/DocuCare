from flask import Flask, request, jsonify
import pandas as pd
import numpy as np
from sklearn.preprocessing import StandardScaler
from sklearn.cluster import AgglomerativeClustering
from sklearn.metrics.pairwise import cosine_similarity

app = Flask(__name__)

PUROK_COORDS = {
    "Purok 1": (14.900573, 120.523604),
    "Purok 2": (14.900155, 120.516251),
    "Purok 3": (14.905449, 120.508226),
    "Purok 4": (14.902110, 120.512887),
    "Purok 5": (14.896526, 120.509381),
}

@app.route("/cluster", methods=["POST"])
def cluster_data():
    """
    Enhanced clustering using hierarchical clustering for trend detection.
    Returns cluster assignments + trend analysis for each purok.
    """
    try:
        data = request.get_json()
        if not data:
            return jsonify({"error": "No data provided"}), 400
        
        df = pd.DataFrame(data)
        
        # Normalize column names
        if "purokName" in df.columns:
            df.rename(columns={"purokName": "purok"}, inplace=True)
        elif "purok_name" in df.columns:
            df.rename(columns={"purok_name": "purok"}, inplace=True)
        
        diseases = ["dengue", "measles", "flu", "allergies", "diarrhea"]
        
        # Ensure all disease columns exist
        for disease in diseases:
            if disease not in df.columns:
                df[disease] = 0
        
        # Get disease matrix
        disease_matrix = df[diseases].values.astype(float)
        
        # Calculate total cases
        df['total_cases'] = disease_matrix.sum(axis=1)
        
        # Hierarchical clustering based on disease patterns
        scaler = StandardScaler()
        disease_scaled = scaler.fit_transform(disease_matrix.T).T
        
        # Determine optimal clusters (2-3 for 5 puroks)
        n_clusters = min(3, len(df))
        
        hierarchical = AgglomerativeClustering(
            n_clusters=n_clusters,
            linkage='ward'
        )
        df["cluster"] = hierarchical.fit_predict(disease_scaled)
        
        # Analyze each cluster
        cluster_info = {}
        for cluster_id in df["cluster"].unique():
            cluster_data = df[df["cluster"] == cluster_id]
            avg_cases = cluster_data[diseases].mean()
            
            # Find dominant diseases (above 50% of max)
            threshold = avg_cases.max() * 0.3
            dominant = avg_cases[avg_cases > threshold].to_dict()
            
            cluster_info[int(cluster_id)] = {
                "puroks": cluster_data["purok"].tolist(),
                "dominant_diseases": dominant,
                "avg_total": float(cluster_data["total_cases"].mean()),
                "size": len(cluster_data)
            }
        
        # Calculate severity level for each purok
        max_cases = df['total_cases'].max()
        df['severity'] = df['total_cases'].apply(
            lambda x: 'high' if x > max_cases * 0.6 
            else 'medium' if x > max_cases * 0.3 
            else 'low'
        )
        
        # Find dominant disease for each purok
        df['dominant_disease'] = df[diseases].idxmax(axis=1)
        df['dominant_count'] = df[diseases].max(axis=1)
        
        # Prepare result
        result = []
        for _, row in df.iterrows():
            result.append({
                "purok": row["purok"],
                "cluster": int(row["cluster"]),
                "severity": row["severity"],
                "total_cases": int(row["total_cases"]),
                "dominant_disease": row["dominant_disease"],
                "dominant_count": int(row["dominant_count"]),
                "cases": {
                    "dengue": int(row["dengue"]),
                    "measles": int(row["measles"]),
                    "flu": int(row["flu"]),
                    "allergies": int(row["allergies"]),
                    "diarrhea": int(row["diarrhea"])
                }
            })
        
        return jsonify({
            "clusters": result,
            "cluster_analysis": cluster_info,
            "summary": {
                "total_puroks": len(df),
                "total_clusters": n_clusters,
                "total_cases": int(df['total_cases'].sum())
            }
        })
        
    except Exception as e:
        print(f"Clustering error: {str(e)}")
        return jsonify({"error": str(e)}), 500

@app.route("/health", methods=["GET"])
def health_check():
    """Health check endpoint"""
    return jsonify({"status": "healthy", "service": "clustering-api"})

if __name__ == "__main__":
    app.run(debug=True, host='0.0.0.0', port=5000)