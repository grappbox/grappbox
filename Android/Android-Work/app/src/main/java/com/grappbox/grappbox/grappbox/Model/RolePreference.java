package com.grappbox.grappbox.grappbox.Model;

import android.annotation.TargetApi;
import android.app.Activity;
import android.app.AlertDialog;
import android.app.Dialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.AsyncTask;
import android.os.Build;
import android.preference.DialogPreference;
import android.preference.Preference;
import android.preference.PreferenceCategory;
import android.preference.PreferenceFragment;
import android.util.AttributeSet;
import android.util.Log;
import android.util.Xml;
import android.view.View;
import android.widget.AdapterView;
import android.widget.EditText;
import android.widget.ListView;

import com.grappbox.grappbox.grappbox.ProjectSettingsActivity;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.ArrayList;

/**
 * Created by wieser_m on 27/01/2016.
 */
public class RolePreference extends DialogPreference {

    PreferenceCategory _parent;
    RoleModel _model;
    String _projectId;
    ProjectSettingsActivity _activity;

    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    public RolePreference(Context context, AttributeSet attrs, int defStyleAttr, int defStyleRes) {
        super(context, attrs, defStyleAttr, defStyleRes);
    }

    public RolePreference(Context context, AttributeSet attrs, int defStyleAttr) {
        super(context, attrs, defStyleAttr);
    }

    public RolePreference(Context context, AttributeSet attrs) {
        super(context, attrs);
    }

    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    public RolePreference(Context context) {
        super(context);
    }

    public void initalize(PreferenceCategory parent, RoleModel model, String projectId, ProjectSettingsActivity activity)
    {
        _parent = parent;
        _model = model;
        _projectId = projectId;
        _activity = activity;
        if (_model.isValid()) {
            setTitle(_model.getName());
            setDialogTitle("");
            setDialogLayoutResource(R.layout.dialog_role_choices);
        }
        else
        {
            setTitle("Add a role");
            setDialogTitle(getContext().getString(R.string.str_role_create_title));
            setDialogLayoutResource(R.layout.dialog_role_create);
        }
    }

    @Override
    protected void onPrepareDialogBuilder(AlertDialog.Builder builder) {
        super.onPrepareDialogBuilder(builder);
        Preference pref = this;
        if (_model.isValid())
            builder.setPositiveButton(null, null);
        else
        {
            builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    Dialog dial = getDialog();
                    EditText name = (EditText) dial.findViewById(R.id.txt_name);

                    if (name.getText().toString().isEmpty())
                        name.setError(getContext().getString(R.string.str_error_empty));
                    else {
                        CreateRoleInProject createTask = new CreateRoleInProject(getContext(), _parent, pref, _projectId);

                        createTask.execute(name.getText().toString());
                        dial.dismiss();
                    }
                }
            });
        }
    }

    public void runRoleFragment()
    {
        Intent intent = new Intent(getContext(), ProjectSettingsActivity.class);
        intent.putExtra(ProjectSettingsActivity.EXTRA_PROJECT_ID, _activity.getModel().getId());
        intent.putExtra(ProjectSettingsActivity.EXTRA_NO_HEADERS, true);
        intent.putExtra(ProjectSettingsActivity.EXTRA_PROJECT_NAME, _activity.getModel().getName());
        intent.putExtra(ProjectSettingsActivity.EXTRA_PROJECT_MODEL, _activity.getModel());
        intent.putExtra(ProjectSettingsActivity.EXTRA_SHOW_FRAGMENT, "com.grappbox.grappbox.grappbox.ProjectSettingsActivity$RolePreferenceFragment");
        _activity.setCurrentSeenRole(_model);
        getContext().startActivity(intent);
    }

    @Override
    protected void onBindDialogView(View view) {
        Preference pref = this;
        super.onBindDialogView(view);
        if (!_model.isValid())
            return;
        ListView list = ((ListView) view.findViewById(R.id.list_role_choices));
        list.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                switch (getContext().getResources().getStringArray(R.array.dialog_roles_choice_opt)[position]) {
                    case "manage_role":
                        runRoleFragment();
                        break;
                    case "delete_role":
                        DeleteProjectRoles deleteTask = new DeleteProjectRoles(getContext(), _parent, pref, _projectId);

                        deleteTask.execute();
                        break;
                    default:
                        break;
                }
                getDialog().dismiss();
            }
        });
    }


    public class DeleteProjectRoles extends AsyncTask<String, Void, String> {
        APIConnectAdapter _api;
        Context _context;
        PreferenceCategory _parent;
        Preference _role;
        String _projectId;

        DeleteProjectRoles(Context context, PreferenceCategory parent, Preference role, String projectId) {
            _context = context;
            _parent = parent;
            _role = role;
            _projectId = projectId;
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
        protected String doInBackground(String... params) {
            _api = APIConnectAdapter.getInstance(true);

            _api.setVersion("V0.2");
            try {
                _api.startConnection("roles/delprojectroles/" + SessionAdapter.getInstance().getToken() + "/" + String.valueOf(_model.getId()));
                _api.setRequestConnection("DELETE");
                return _api.getInputSream();
            } catch (IOException e) {
                e.printStackTrace();
            }
            return null;
        }

        @Override
        protected void onPostExecute(String s) {
            JSONObject json, info;

            if (s == null || s.isEmpty())
            {
                return;
            }
            try {
                json = new JSONObject(s);
                info = json.getJSONObject("info");
                if (disconnectAPI())
                    return;
                assert info != null;
                if (handleAPIError(info))
                    return;
                _parent.removePreference(_role);
                return;
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            super.onPostExecute(s);
        }
    }

    public class CreateRoleInProject extends AsyncTask<String, Void, String> {
        APIConnectAdapter _api;
        Context _context;
        PreferenceCategory _parent;
        Preference _role;
        String _projectId;

        CreateRoleInProject(Context context, PreferenceCategory parent, Preference role, String projectId) {
            _context = context;
            _parent = parent;
            _role = role;
            _projectId = projectId;
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
        protected String doInBackground(String... params) {
            if (params.length < 1)
                return null;
            _api = APIConnectAdapter.getInstance(true);
            JSONObject json = new JSONObject();
            JSONObject data = new JSONObject();
            String name = params[0];

            _api.setVersion("V0.2");
            try {
                data.put("token", SessionAdapter.getInstance().getToken());
                data.put("projectId", _projectId);
                data.put("name", name);

                data.put("teamTimeline", 0);
                data.put("customerTimeline", 0);
                data.put("gantt", 0);
                data.put("whiteboard", 0);
                data.put("bugtracker", 0);
                data.put("event", 0);
                data.put("task", 0);
                data.put("projectSettings", 0);
                data.put("cloud", 0);
                json.put("data", data);
                _api.startConnection("roles/addprojectroles");
                _api.setRequestConnection("POST");
                _api.sendJSON(json);
                _model.setName(name);
                return _api.getInputSream();
            } catch (IOException | JSONException e) {
                e.printStackTrace();
            }
            return null;
        }

        @Override
        protected void onPostExecute(String s) {
            JSONObject json, info, data;

            if (s == null || s.isEmpty())
            {
                return;
            }
            try {
                json = new JSONObject(s);
                info = json.getJSONObject("info");
                if (disconnectAPI())
                    return;
                assert info != null;
                if (handleAPIError(info))
                    return;
                data = json.getJSONObject("data");
                _model.setId(data.getInt("id"));
                if (_role instanceof RolePreference)
                    ((RolePreference) _role).initalize(_parent, _model, _projectId, _activity);
                //Add create button
                RolePreference pref = new RolePreference(_context, Xml.asAttributeSet(_context.getResources().getLayout(R.layout.dialog_role_create)));
                pref.initalize(_parent, new RoleModel(), _projectId, _activity);
                _parent.addPreference(pref);
                runRoleFragment();
                return;
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            super.onPostExecute(s);
        }
    }
}
