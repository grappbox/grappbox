package com.grappbox.grappbox.grappbox.Model;

import android.annotation.TargetApi;
import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.os.AsyncTask;
import android.os.Build;
import android.preference.Preference;
import android.preference.SwitchPreference;
import android.util.AttributeSet;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CompoundButton;
import android.widget.Switch;

import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by wieser_m on 27/01/2016.
 */
public class UserRolePreference extends SwitchPreference {

    RoleModel _model;
    boolean _acquired;
    int _userId;
    int _projectId;

    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    public UserRolePreference(Context context, AttributeSet attrs, int defStyleAttr, int defStyleRes) {
        super(context, attrs, defStyleAttr, defStyleRes);
    }

    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    public UserRolePreference(Context context, AttributeSet attrs, int defStyleAttr) {
        super(context, attrs, defStyleAttr);
    }

    public UserRolePreference(Context context, AttributeSet attrs) {
        super(context, attrs);
    }

    public UserRolePreference(Context context) {
        super(context);
    }

    private Switch findSwitchWidget(View view){
        if (view instanceof  Switch){
            return (Switch)view;
        }
        if (view instanceof ViewGroup){
            ViewGroup viewGroup = (ViewGroup)view;
            for (int i = 0; i < viewGroup.getChildCount();i++){
                View child = viewGroup.getChildAt(i);
                if (child instanceof ViewGroup){
                    Switch result = findSwitchWidget(child);
                    if (result != null) return result;
                }
                if (child instanceof Switch) {
                    return (Switch) child;
                }
            }
        }
        return null;
    }

    public void setRoleModel(RoleModel model, boolean acquired, int projectId, int userId) {
        _model = model;
        _acquired = acquired;
        _userId = userId;
        _projectId = projectId;
        if (_model == null || !_model.isValid())
            return;
        setTitle(_model.getName());
        setChecked(_acquired);
        setPersistent(false);


    }

    @Override
    protected void onBindView(View view) {
        super.onBindView(view);

       setOnPreferenceClickListener(new OnPreferenceClickListener() {
            @Override
            public boolean onPreferenceClick(Preference preference) {
                if (isChecked()) {
                    AssignPersonRoleTask assignTask = new AssignPersonRoleTask(getContext());
                    assignTask.execute();
                } else {
                    DeletePersonRoleTask deleteTask = new DeletePersonRoleTask(getContext());
                    deleteTask.execute();
                }
                return false;
            }
        });
        Switch switcher = findSwitchWidget(view);
        switcher.setOnCheckedChangeListener(new CompoundButton.OnCheckedChangeListener() {
            @Override
            public void onCheckedChanged(CompoundButton buttonView, boolean isChecked) {
                if (isChecked) {
                    AssignPersonRoleTask assignTask = new AssignPersonRoleTask(getContext());
                    assignTask.execute();
                } else {
                    DeletePersonRoleTask deleteTask = new DeletePersonRoleTask(getContext());
                    deleteTask.execute();
                }
            }
        });
    }

    public class DeletePersonRoleTask extends AsyncTask<String, Void, String>
    {
        APIConnectAdapter _api;
        Context _context;

        DeletePersonRoleTask(Context context)
        {
            _context = context;
        }

        private boolean handleAPIError(JSONObject infos) throws JSONException {
            if (!infos.getString("return_code").startsWith("1.")) {
                AlertDialog.Builder builder = new AlertDialog.Builder(_context);

                builder.setMessage(_context.getString(R.string.problem_grappbox_server) + _context.getString(R.string.error_code_head) + infos.getString("return_code"));
                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                });
                builder.create().show();
                return true;
            }
            return false;
        }

        private boolean disconnectAPI() throws IOException {
            int responseCode = 500;

            responseCode = _api.getResponseCode();
            if (responseCode < 300) {
                APIConnectAdapter.getInstance().closeConnection();
            } else {
                AlertDialog.Builder builder = new AlertDialog.Builder(_context);

                builder.setMessage(_context.getString(R.string.problem_grappbox_server) + _context.getString(R.string.error_code_head) + String.valueOf(responseCode));
                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                });
                builder.create().show();
                return true;
            }
            return false;
        }

        @Override
        protected void onPostExecute(String s) {
            super.onPostExecute(s);
            JSONObject json, info;

            if (s == null || s.isEmpty())
            {
                setChecked(_acquired);
                return;
            }
            try {
                json = new JSONObject(s);
                info = json.getJSONObject("info");
                if (disconnectAPI())
                    return;
                assert info != null;
                if (handleAPIError(info))
                {
                    setChecked(_acquired);
                    return;
                }
                _acquired = false;
                setChecked(false);
            } catch (JSONException | IOException e)
            {
                e.printStackTrace();
            }
        }

        @Override
        protected String doInBackground(String... params) {
            _api = APIConnectAdapter.getInstance(true);

            _api.setVersion("V0.2");
            try {
                _api.startConnection("roles/delpersonrole/" + SessionAdapter.getInstance().getToken() + "/" + String.valueOf(_projectId) + "/" + String.valueOf(_userId) + "/" + String.valueOf(_model.getId()));
                _api.setRequestConnection("DELETE");
                return _api.getInputSream();
            } catch (IOException e) {
                e.printStackTrace();
            }
            return null;
        }
    }

    public class AssignPersonRoleTask extends AsyncTask<String, Void, String>
    {
        APIConnectAdapter _api;
        Context _context;

        AssignPersonRoleTask(Context context)
        {
            _context = context;
        }

        private boolean handleAPIError(JSONObject infos) throws JSONException {
            if (!infos.getString("return_code").startsWith("1.") && !infos.getString("return_code").equals("13.5.7")) {
                AlertDialog.Builder builder = new AlertDialog.Builder(_context);

                builder.setMessage(_context.getString(R.string.problem_grappbox_server) + _context.getString(R.string.error_code_head) + infos.getString("return_code"));
                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                });
                builder.create().show();
                return true;
            }
            return false;
        }

        private boolean disconnectAPI() throws IOException {
            int responseCode = 500;

            responseCode = _api.getResponseCode();
            if (responseCode < 300) {
                APIConnectAdapter.getInstance().closeConnection();
            } else {
                AlertDialog.Builder builder = new AlertDialog.Builder(_context);

                builder.setMessage(_context.getString(R.string.problem_grappbox_server) + _context.getString(R.string.error_code_head) + String.valueOf(responseCode));
                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                });
                builder.create().show();
                return true;
            }
            return false;
        }

        @Override
        protected void onPostExecute(String s) {
            super.onPostExecute(s);

            JSONObject json, info;
            if (s == null || s.isEmpty())
            {
                setChecked(_acquired);
                return;
            }
            try {
                json = new JSONObject(s);
                info = json.getJSONObject("info");
                if (disconnectAPI())
                    return;
                assert info != null;
                if (handleAPIError(info))
                {
                    setChecked(_acquired);
                    return;
                }
                _acquired = true;
                setChecked(true);
            } catch (JSONException | IOException e)
            {
                e.printStackTrace();
            }
        }

        @Override
        protected String doInBackground(String... params) {
            _api = APIConnectAdapter.getInstance(true);
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();

            _api.setVersion("V0.2");
            try {
                data.put("token", SessionAdapter.getInstance().getToken());
                data.put("userId", _userId);
                data.put("roleId", _model.getId());
                json.put("data", data);
                _api.startConnection("roles/assignpersontorole");
                _api.setRequestConnection("POST");
                _api.sendJSON(json);
                return _api.getInputSream();
            } catch (IOException | JSONException e) {
                e.printStackTrace();
            }
            return null;
        }
    }
}
