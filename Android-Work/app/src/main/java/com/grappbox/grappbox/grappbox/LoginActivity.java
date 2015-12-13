package com.grappbox.grappbox.grappbox;

import android.app.DialogFragment;
import android.content.ContentValues;
import android.content.Intent;
import android.content.pm.PackageInstaller;
import android.os.AsyncTask;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.EditText;

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
            ContentValues contentAPI = null;
            String resultAPI;

            try {
                APIConnectAdapter.getInstance().startConnection("accountadministration/login");
                APIConnectAdapter.getInstance().setRequestConnection("POST");

                JSONObject JSONParam = new JSONObject();
                JSONParam.put("login", param[0]);
                JSONParam.put("password", param[1]);
                APIConnectAdapter.getInstance().sendJSON(JSONParam);
                Log.v("Test", "Connection");
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
                if ((contentAPI = getLoginDataFromJSON(resultAPI)) == null)
                    return null;

            } catch (IOException e){
                Log.e("APIConnection", "Error ", e);
                return null;
            } catch (JSONException j){
                Log.e("APIConnection", "Error ", j);
                return null;
            } finally {
                APIConnectAdapter.getInstance().closeConnection();
            }
            return contentAPI;
        }

    }
}
