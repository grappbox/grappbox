package com.grappbox.grappbox.grappbox.Model;

import android.annotation.TargetApi;
import android.content.Context;
import android.content.DialogInterface;
import android.os.AsyncTask;
import android.os.Build;
import android.preference.DialogPreference;
import android.util.AttributeSet;
import android.util.Log;
import android.widget.EditText;
import android.widget.Toast;

import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by tan_f on 14/07/2016.
 */
public class UserPasswordPreference extends DialogPreference {

    private String txtNewPass;
    private String txtConfirmPass;

    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    public UserPasswordPreference(Context context, AttributeSet attrs, int defStyleAttr, int defStyleRes) {
        super(context, attrs, defStyleAttr, defStyleRes);
        setPersistent(false);
    }

    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    public UserPasswordPreference(Context context) {
        super(context);
        setPersistent(false);
    }

    public UserPasswordPreference(Context context, AttributeSet attrs, int defStyleAttr) {
        super(context, attrs, defStyleAttr);
        setPersistent(false);
    }

    public UserPasswordPreference(Context context, AttributeSet attrs) {
        super(context, attrs);
        setPersistent(false);
    }

    @Override
    public void onClick(DialogInterface dialog, int which) {
        super.onClick(dialog, which);
        txtNewPass = ((EditText) getDialog().findViewById(R.id.txt_new_password)).getText().toString();
        txtConfirmPass = ((EditText) getDialog().findViewById(R.id.txt_confirm_password)).getText().toString();
    }

    @Override
    protected void onDialogClosed(boolean positiveResult) {
        if (positiveResult) {
            if (txtNewPass.equals(txtConfirmPass) && !txtConfirmPass.equals("")) {
                APIRequestUpdateUserPassword profile = new APIRequestUpdateUserPassword();
                profile.execute(SessionAdapter.getInstance().getPassword(), txtNewPass);
            }
        }
        super.onDialogClosed(positiveResult);
    }

    public class APIRequestUpdateUserPassword extends AsyncTask<String, Void, String> {

        private final static String _PATH = "user/basicinformations/";
        private String newPass;
        private String oldPass;
        private Integer _APIResponse;

        APIRequestUpdateUserPassword() {

        }

        @Override
        protected void onPostExecute(String result) {
            super.onPostExecute(result);
            if (result == null || _APIResponse != 200) {
                Toast.makeText(getContext(), "An error occur, password change failed", Toast.LENGTH_SHORT).show();
                SessionAdapter.getInstance().setPassword(oldPass);
            }
            Toast.makeText(getContext(), "Change password succeed", Toast.LENGTH_SHORT).show();
            SessionAdapter.getInstance().setPassword(newPass);
        }

        @Override
        protected String doInBackground(String... param) {
            String resultAPI = null;
            if (param[0] == null || param[1] == null ||
                    param[2] == null || param[3] == null)
                return null;
            try {
                APIConnectAdapter.getInstance().startConnection(_PATH + SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN));
                APIConnectAdapter.getInstance().setRequestConnection("PUT");

                JSONObject JSONData = new JSONObject();
                JSONObject JSONParam = new JSONObject();
                oldPass = param[0];
                newPass = param[1];
                JSONParam.put("oldPassword", oldPass);
                JSONParam.put("password", newPass);
                JSONData.put("data", JSONParam);

                APIConnectAdapter.getInstance().sendJSON(JSONData);
                _APIResponse = APIConnectAdapter.getInstance().getResponseCode();
                Log.v("JSON Response", String.valueOf(_APIResponse));
                if (_APIResponse == 200 || _APIResponse == 206)
                    resultAPI = APIConnectAdapter.getInstance().getInputSream();
                Log.v("JSON Content", resultAPI);
            } catch (IOException | JSONException e) {
                e.printStackTrace();
            } finally {
                APIConnectAdapter.getInstance().closeConnection();
            }
            return resultAPI;
        }
    }
}
