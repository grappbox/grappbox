package com.grappbox.grappbox.grappbox;

import android.app.AlertDialog;
import android.app.Dialog;
import android.app.DialogFragment;
import android.content.ContentValues;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.pm.PackageInstaller;
import android.os.AsyncTask;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.EditText;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;

public class LoginActivity extends AppCompatActivity {

    EditText    _login;
    EditText    _passw;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);
    }

    public void LoginUser(View view)
    {
        _login = (EditText) findViewById(R.id.loginInput);
        _passw = (EditText) findViewById(R.id.passwInput);

        APIRequestLogin api = new APIRequestLogin();
        api.execute(_login.getText().toString(), _passw.getText().toString());
    }

    private void LoginError()
    {
        DialogFragment loginError = new LoginErrorAlertFragment();
        loginError.show(getFragmentManager(), "LoginError");
    }

    private void AccesUser()
    {
        Intent intent = new Intent(this, MainActivity.class);
        startActivity(intent);
    }

    public class APIRequestLogin extends AsyncTask<String, Void, ContentValues> {

        private static final String _API_URL_BASE = "http://api.grappbox.com/app_dev.php/";

        private ContentValues  getLoginDataFromJSON(String resultJSON) throws JSONException
        {
            final String DATA_LIST = "user";
            final String[] DATA_USER = {"id", "firstname", "lastname", "email", "token"};

            ContentValues JSONContent = new ContentValues();
            JSONObject jsonObject = new JSONObject(resultJSON);
            JSONObject userData = jsonObject.getJSONObject(DATA_LIST);

            for (String data : DATA_USER) {
                if (userData.getString(data) == null)
                    return null;
                JSONContent.put(data, userData.getString(data));
            }

            return JSONContent;
        }

        @Override
        protected void onPostExecute(ContentValues result) {
            super.onPostExecute(result);
            if (result == null
                    || !result.containsKey("id")
                    || !result.containsKey("firstname")
                    || !result.containsKey("lastname")
                    || !result.containsKey("token")) {
                LoginError();
            } else {
                SessionAdapter.getInstance().LogInUser(Float.parseFloat(result.get("id").toString()),
                        result.get("firstname").toString(),
                        result.get("lastname").toString(),
                        result.get("token").toString());
                AccesUser();
            }
        }

        @Override
        protected ContentValues doInBackground(String ... param)
        {
            HttpURLConnection connection = null;
            BufferedReader reader = null;
            ContentValues contentAPI = null;
            String resultAPI;

            try {

                URL url = new URL("http://api.grappbox.com/app_dev.php/V0.8/accountadministration/login");
                connection = (HttpURLConnection)url.openConnection();
                connection.setRequestMethod("POST");
                connection.setDoInput(true);
                connection.setDoOutput(true);

                DataOutputStream dataOutputStream;

                JSONObject JSONParam = new JSONObject();
                JSONParam.put("login", param[0]);
                JSONParam.put("password", param[1]);

                dataOutputStream = new DataOutputStream(connection.getOutputStream());
                dataOutputStream.write(JSONParam.toString().getBytes("UTF-8"));
                dataOutputStream.flush();
                dataOutputStream.close();
                connection.connect();
                // Read the input stream into a String

                InputStream inputStream = connection.getInputStream();
                StringBuffer buffer = new StringBuffer();
                if (inputStream == null) {
                    return null;
                }
                reader = new BufferedReader(new InputStreamReader(inputStream));

                String line;
                String nLine;
                while ((line = reader.readLine()) != null) {
                    nLine = line + "\n";
                    buffer.append(nLine);
                }

                if (buffer.length() == 0) {
                    return null;
                }

                resultAPI = buffer.toString();
                Log.v("Connection result : ", resultAPI);
                if ((contentAPI = getLoginDataFromJSON(resultAPI)) == null)
                    return null;

            } catch (IOException e){
                Log.e("APIConnection", "Error ", e);
                return null;
            } catch (JSONException j){
                Log.e("APIConnection", "Error ", j);
                return null;
            } finally {
                if (connection != null){
                    connection.disconnect();
                }
                if (reader != null){
                    try {
                        reader.close();
                    } catch (final IOException e){
                        Log.e("APIConnection", "Error ", e);
                    }
                }
            }
            return contentAPI;
        }

    }
}
