package com.grappbox.grappbox.grappbox.Model;

import android.annotation.TargetApi;
import android.app.Activity;
import android.app.AlertDialog;
import android.app.Dialog;
import android.content.Context;
import android.content.DialogInterface;
import android.os.AsyncTask;
import android.os.Build;
import android.preference.DialogPreference;
import android.util.AttributeSet;
import android.util.Log;
import android.widget.EditText;

import com.grappbox.grappbox.grappbox.MainActivity;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by wieser_m on 25/01/2016.
 */
public class SafePasswordPreference extends DialogPreference {

    private String txtNewPass;
    private String txtConfirmPass;
    private int _projectId;

    public void setProjectId(int projectId){ _projectId = projectId; }

    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    public SafePasswordPreference(Context context, AttributeSet attrs, int defStyleAttr, int defStyleRes) {
        super(context, attrs, defStyleAttr, defStyleRes);
        setPersistent(false);
    }

    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    public SafePasswordPreference(Context context) {
        super(context);
        setPersistent(false);
    }

    public SafePasswordPreference(Context context, AttributeSet attrs, int defStyleAttr) {
        super(context, attrs, defStyleAttr);
        setPersistent(false);
    }

    public SafePasswordPreference(Context context, AttributeSet attrs) {
        super(context, attrs);
        setPersistent(false);
    }

    @Override
    public void onClick(DialogInterface dialog, int which) {
        super.onClick(dialog, which);
        txtNewPass = ((EditText)getDialog().findViewById(R.id.txt_new_password)).getText().toString();
        txtConfirmPass = ((EditText) getDialog().findViewById(R.id.txt_confirm_password)).getText().toString();
    }

    @Override
    protected void onDialogClosed(boolean positiveResult) {
        if (positiveResult)
        {

            if (txtNewPass.equals(txtConfirmPass) && !txtConfirmPass.equals(""))
            {
                UpdateSafePassword task = new UpdateSafePassword(getContext(), _projectId);

                task.execute(txtConfirmPass);
            }
        }
        super.onDialogClosed(positiveResult);
    }

    public static class UpdateSafePassword extends AsyncTask<String, Void, String>
    {
        APIConnectAdapter _api;
        Context _context;
        int      _projectId;

        UpdateSafePassword(Context context, int projectId)
        {
            _context = context;
            _projectId = projectId;
        }

        private boolean handleAPIError(JSONObject infos) throws JSONException {
            if (!infos.getString("return_code").startsWith("1."))
            {
                AlertDialog.Builder builder = new AlertDialog.Builder(_context);

                builder.setMessage(_context.getString(R.string.problem_grappbox_server) + _context.getString(R.string.error_code_head) + infos.getString("return_code"));
                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                });
                builder.create().show();
                if (_context instanceof MainActivity)
                {
                    ((MainActivity) _context).logoutUser();
                }
                return true;
            }
            return false;
        }

        private boolean disconnectAPI() throws IOException {
            int responseCode = 500;

            responseCode = _api.getResponseCode();
            if (responseCode < 300) {
                APIConnectAdapter.getInstance().closeConnection();
            }
            else
            {
                AlertDialog.Builder builder = new AlertDialog.Builder(_context);

                builder.setMessage(_context.getString(R.string.problem_grappbox_server) + _context.getString(R.string.error_code_head) + String.valueOf(responseCode));
                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                });
                builder.create().show();
                if (_context instanceof MainActivity)
                    ((MainActivity) _context).logoutUser();
                return true;
            }
            return false;
        }

        @Override
        protected void onPostExecute(String s) {
            assert s != null;
            JSONObject json, info;

            try {
                json = new JSONObject(s);
                info = json.getJSONObject("info");
                if (disconnectAPI())
                    return;
                assert info != null;
                if (handleAPIError(info))
                    return;
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            super.onPostExecute(s);
        }

        @Override
        protected String doInBackground(String... params) {
            if (params.length < 1)
                return null;
            String value = params[0];
            JSONObject json, data;

            _api = APIConnectAdapter.getInstance(true);
            data = new JSONObject();
            json = new JSONObject();
            try {
                data.put("token", SessionAdapter.getInstance().getToken());
                data.put("projectId", _projectId);
                data.put("password", value);
                json.put("data", data);
                _api.setVersion("V0.2");
                _api.startConnection("projects/updateinformations");
                _api.setRequestConnection("PUT");
                _api.sendJSON(json);
                return _api.getInputSream();
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            return null;
        }
    }
}
