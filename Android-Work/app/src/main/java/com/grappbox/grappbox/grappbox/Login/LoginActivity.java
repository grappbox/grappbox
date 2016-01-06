package com.grappbox.grappbox.grappbox.Login;

import android.app.Activity;
import android.app.DialogFragment;
import android.content.ContentValues;
import android.content.Intent;
import android.os.AsyncTask;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.EditText;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.MainActivity;
import com.grappbox.grappbox.grappbox.R;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

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

        APIRequestLogin api = new APIRequestLogin(this);
        api.execute(_login.getText().toString(), _passw.getText().toString());
    }

    private class APIRequestLogin extends AsyncTask<String, Void, ContentValues> {

        private Activity _login;

        public APIRequestLogin(Activity activity)
        {
            _login = activity;
        }

        @Override
        protected void onPostExecute(ContentValues result) {
            super.onPostExecute(result);
            if (result == null
                    || !result.containsKey("id")
                    || !result.containsKey("firstname")
                    || !result.containsKey("lastname")
                    || !result.containsKey("token")) {
                DialogFragment loginError = new LoginErrorAlertFragment();
                loginError.show(_login.getFragmentManager(), "LoginError");
            } else {
                SessionAdapter.getInstance().LogInUser(result.get("id").toString(),
                        result.get("firstname").toString(),
                        result.get("lastname").toString(),
                        result.get("token").toString(),
                        result.get("login").toString(),
                        result.get("password").toString());
                Intent intent = new Intent(_login, MainActivity.class);
                _login.startActivity(intent);
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
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
                if ((contentAPI = APIConnectAdapter.getInstance().getLoginData(resultAPI)) == null)
                    return null;
                contentAPI.put("login", param[0]);
                contentAPI.put("password", param[1]);

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
