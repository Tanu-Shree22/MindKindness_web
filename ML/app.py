from flask import Flask, render_template, request
import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import LabelEncoder
import joblib

app = Flask(__name__)

# Load the synthetic dataset
df = pd.read_csv('medical_history_data.csv')

# Assume 'Mental Health Condition' is the target variable
# Replace None values with a default value (for simplicity)
df['Mental Health Condition'].fillna(-1, inplace=True)

# Assume 'Quiz Score' is a feature
# Replace None values with the mean value (for simplicity)
df['Quiz Score'].fillna(df['Quiz Score'].mean(), inplace=True)

# Encode categorical columns
df_encoded = pd.get_dummies(df, columns=['Medical History', 'Major Loss'])

# Define features (X) and target variable (y)
X = df_encoded.drop('Mental Health Condition', axis=1)
y = df_encoded['Mental Health Condition']

# Encode the target variable with LabelEncoder
label_encoder = LabelEncoder()
y_encoded = label_encoder.fit_transform(y)

# Split the dataset into training and testing sets
X_train, X_test, y_train, y_test = train_test_split(X, y_encoded, test_size=0.2, random_state=42)

# Create and train the Random Forest model
rf_model = RandomForestClassifier(n_estimators=100, random_state=42)
rf_model.fit(X_train, y_train)

# Save the trained Random Forest model
joblib.dump(rf_model, 'rf_model.joblib')

# Save the LabelEncoder
joblib.dump(label_encoder, 'label_encoder.joblib')

# Set feature names explicitly to avoid warning
rf_model.feature_names_in_ = list(X_train.columns)

# Mapping for Medical History and Major Loss
medical_history_mapping = {'diabetes': 1, 'thyroid': 2, 'cancer': 3, 'HIV/AIDS': 4, 'sleep disorder': 5, 'any other': 6}
major_loss_mapping = {'death of loved ones': 1, 'divorce': 2, 'relationship breakup': 3, 'loss of employment': 4, 'financial loss': 5, 'miscarriage': 6, 'any other': 7}

# Home route - Displays the input form
@app.route('/')
def home():
    return render_template('index.html')

# Prediction route - Handles form submission and displays the result
@app.route('/predict', methods=['POST'])
def predict():
    try:
        # Retrieve user input from the form
        medical_history = request.form['medical_history']
        major_loss = request.form['major_loss']
        quiz_score = float(request.form['quiz_score'])

        # Preprocess user input
        user_input = pd.DataFrame({'Quiz Score': [quiz_score],
                                   'Medical History': [medical_history_mapping.get(medical_history, 0)],
                                   'Major Loss': [major_loss_mapping.get(major_loss, 0)]})

        # One-hot encode user input features
        user_input_encoded = pd.get_dummies(user_input, columns=['Medical History', 'Major Loss'])

        # Ensure columns are consistent with the model's features
        missing_columns = set(rf_model.feature_names_in_) - set(user_input_encoded.columns)
        for column in missing_columns:
            user_input_encoded[column] = 0

        # Reorder columns to match the order in the training data
        user_input_encoded = user_input_encoded[rf_model.feature_names_in_]

        # Make prediction
        predicted_value_encoded = rf_model.predict(user_input_encoded.to_numpy().reshape(1, -1))[0]

        # Decode the predicted value
        predicted_value = label_encoder.inverse_transform([predicted_value_encoded])[0]

        # Determine the prediction result
        prediction_result = "Patient needs mental treatment." if predicted_value >= 2 else "Patient does not need mental treatment."

        return render_template('result.html', prediction_result=prediction_result)

    except ValueError as e:
        return render_template('error.html', error=str(e))

if __name__ == '__main__':
    app.run(debug=True)
