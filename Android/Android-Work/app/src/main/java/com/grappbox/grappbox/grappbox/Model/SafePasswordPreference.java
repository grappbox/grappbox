package com.grappbox.grappbox.grappbox.Model;

import android.annotation.TargetApi;
import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.os.AsyncTask;
import android.os.Build;
import android.preference.DialogPreference;
import android.util.AttributeSet;
import android.util.Log;
import android.widget.EditText;
import android.widget.Toast;

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
    private String txtOldPass;
    private String _projectId;

    public void setProjectId(String projectId){ _projectId = projectId; }

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
        txtOldPass = ((EditText) getDialog().findViewById(R.id.txt_old_pass)).getText().toString();
    }

    @Override
    protected void onDialogClosed(boolean positiveResult) {
        if (positiveResult)
        {
            if (txtNewPass.equals(txtConfirmPass) && !txtConfirmPass.equals("") && !txtOldPass.equals(""))
            {
                UpdateSafePassword task = new UpdateSafePassword(getContext(), _projectId);

                task.execute(txtConfirmPass, txtOldPass);
            }
            else if (txtOldPass.isEmpty() || txtNewPass.isEmpty())
            {
                Toast.makeText(getContext(), "One field was empty, the request has been ignored", Toast.LENGTH_SHORT).show();
            }
        }
        super.onDialogClosed(positiveResult);
    }

    public static class UpdateSafePassword extends AsyncTask<String, Void, String>
    {
        APIConnectAdapter _api;
        Context _context;
        String _projectId;

        UpdateSafePassword(Context context, String projectId)
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
            if (s == null){
                Toast.makeText(_context, "We had an unexpected problem with GrappBox Server, please try it later", Toast.LENGTH_SHORT).show();
                return;
            }
            JSONObject json, info;

            try {
                json = new JSONObject(s);
                info = json.getJSONObject("info");
                if (disconnectAPI())
                    return;
                assert info != null;
                if (handleAPIError(info))
                    return;
                Toast.makeText(_context, "Project's safe password has been successfully updated", Toast.LENGTH_SHORT).show();
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            super.onPostExecute(s);
        }

        @Override
        protected String doInBackground(String... params) {
            if (params.length < 2)
                return null;
            String value = params[0];
            String oldPass = params[1];
            JSONObject json, data;

            _api = APIConnectAdapter.getInstance(true);
            data = new JSONObject();
            json = new JSONObject();
            try {
                Log.e("Test", "Selected Project : " + SessionAdapter.getInstance().getCurrentSelectedProject());
                data.put("token", SessionAdapter.getInstance().getToken());
                data.put("projectId", SessionAdapter.getInstance().getCurrentSelectedProject());
                data.put("password", value);
                data.put("oldPassword", oldPass);
                json.put("data", data);
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
