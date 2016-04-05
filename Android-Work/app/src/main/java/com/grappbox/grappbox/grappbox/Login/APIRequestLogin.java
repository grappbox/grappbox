package com.grappbox.grappbox.grappbox.Login;

import android.app.Activity;
import android.app.DialogFragment;
import android.content.ContentValues;
import android.content.Intent;
import android.os.AsyncTask;
import android.os.Debug;
import android.util.Log;

import com.grappbox.grappbox.grappbox.MainActivity;
import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

class APIRequestLogin extends AsyncTask<String, Void, String> {

    private Activity _loginActivity;
    private String _login;
    private String _password;

    public APIRequestLogin(Activity activity)
    {
        _loginActivity = activity;
    }

    @Override
    protected void onPostExecute(String result) {
        super.onPostExecute(result);
        final String[] DATA_USER = {"id", "firstname", "lastname", "email", "token"};

        if (result == null) {
            DialogFragment loginError = new LoginErrorAlertFragment();
            loginError.show(_loginActivity.getFragmentManager(), "LoginError");
            return;
        }
        Log.v("Login JSON", result);
        try {
            ContentValues userInformation = new ContentValues();
            JSONObject jsonObject = new JSONObject(result);
            JSONObject userData = jsonObject.getJSONObject("data");

            for (String data : DATA_USER) {
                userInformation.put(data, userData.getString(data));
            }
            userInformation.put("login", _login);
            userInformation.put("password", _password);

            SessionAdapter.getInstance().LogInUser(userInformation.get("id").toString(),
                    userInformation.get("firstname").toString(),
                    userInformation.get("lastname").toString(),
                    userInformation.get("token").toString(),
                    userInformation.get("login").toString(),
                    userInformation.get("password").toString());
            Intent intent = new Intent(_loginActivity, MainActivity.class);
            _loginActivity.startActivity(intent);
        } catch (JSONException j){
            Log.v("JSON error", "Exception");
        }
    }

    @Override
    protected String doInBackground(String ... param)
    {
        Integer APIResponse;
        String resultAPI = null;

        try {
            APIConnectAdapter.getInstance().startConnection("accountadministration/login");
            APIConnectAdapter.getInstance().setRequestConnection("POST");

            JSONObject JSONParam = new JSONObject();
            JSONObject JSONData = new JSONObject();
            _login = param[0];
            _password = param[1];
            JSONParam.put("login", _login);
            JSONParam.put("password", _password);
            JSONData.put("data", JSONParam);
            APIConnectAdapter.getInstance().sendJSON(JSONData);
            APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            if (APIResponse == 200) {
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
            }

        } catch (IOException e){
            Log.e("APIConnection", "Error ", e);
            return null;
        } catch (JSONException j){
            Log.e("APIConnection", "Error ", j);
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }
}
