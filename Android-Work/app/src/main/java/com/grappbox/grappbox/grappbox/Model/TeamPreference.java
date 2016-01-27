package com.grappbox.grappbox.grappbox.Model;

import android.annotation.TargetApi;
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
import android.util.AttributeSet;
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
import java.net.ProtocolException;

/**
 * Created by wieser_m on 27/01/2016.
 */
public class TeamPreference extends DialogPreference {

    UserModel _user;
    ProjectSettingsActivity _activity;
    PreferenceCategory _parent;
    int _projectId;

    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    public TeamPreference(Context context, AttributeSet attrs, int defStyleAttr, int defStyleRes) {
        super(context, attrs, defStyleAttr, defStyleRes);
    }

    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    public TeamPreference(Context context) {
        super(context);
    }

    public TeamPreference(Context context, AttributeSet attrs, int defStyleAttr) {
        super(context, attrs, defStyleAttr);
    }

    public TeamPreference(Context context, AttributeSet attrs) {
        super(context, attrs);
    }

    public void setCategory(PreferenceCategory parent)
    {
        _parent = parent;
    }

    public void setUserModel(int projectId, ProjectSettingsActivity activity, UserModel model)
    {
        _activity = activity;
        _projectId = projectId;
        _user = model;
        assert _user != null;
        if (_user.isValid())
        {
            setDialogLayoutResource(R.layout.dialog_manage_team_member);
            setSummary("");
            setDialogTitle("");
            setTitle(_user.getCompleteName());
        }
        else
        {
            setDialogLayoutResource(R.layout.dialog_add_team_member);
            setSummary("");
            setDialogTitle(R.string.str_add_team_member);
            setTitle(R.string.str_add_team_member);
        }
    }

    @Override
    protected View onCreateDialogView() {
        Preference pref = this;
        View v =  super.onCreateDialogView();
        if (!_user.isValid())
            return v;

        ((ListView) v.findViewById(R.id.list_manage_team_member)).setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                switch (v.getResources().getStringArray(R.array.dialog_add_team_member_pref_choices_enum)[position])
                {
                    case "manage_roles":
                        _activity.setCurrentSeenUser(_user);
                        Intent intent = new Intent(getContext(), ProjectSettingsActivity.class);
                        intent.putExtra(ProjectSettingsActivity.EXTRA_PROJECT_ID, ((ProjectModel) _activity.getModel()).getId());
                        intent.putExtra(ProjectSettingsActivity.EXTRA_NO_HEADERS, true);
                        intent.putExtra(ProjectSettingsActivity.EXTRA_PROJECT_NAME, ((ProjectModel) _activity.getModel()).getName());
                        intent.putExtra(ProjectSettingsActivity.EXTRA_PROJECT_MODEL, (ProjectModel) _activity.getModel());
                        intent.putExtra(ProjectSettingsActivity.EXTRA_SHOW_FRAGMENT, "com.grappbox.grappbox.grappbox.ProjectSettingsActivity$TeamPreferenceFragment");
                        getContext().startActivity(intent);

                        break;
                    case "delete_access":
                        DeleteUserToProjectTask deleteTask = new DeleteUserToProjectTask(pref, getContext(), _projectId);

                        deleteTask.execute();
                        getDialog().dismiss();
                        break;
                    default:
                        break;
                }
                getDialog().dismiss();
            }
        });
        return v;
    }

    @Override
    protected void onPrepareDialogBuilder(AlertDialog.Builder builder) {
        super.onPrepareDialogBuilder(builder);
        TeamPreference pref = this;
        if (_user.isValid()){
            builder.setPositiveButton(null, null);
            return;
        }
        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                Dialog d = getDialog();
                String email = ((EditText)d.findViewById(R.id.txt_member_name)).getText().toString();

                if (email.isEmpty())
                    return;
                AddUserToProjectTask addTask = new AddUserToProjectTask(pref, getContext(), _projectId);
                addTask.execute(email);
            }
        });

    }

    public class DeleteUserToProjectTask extends AsyncTask<String, Void, String>
    {
        Context _context;
        APIConnectAdapter _api;
        int _projectId;
        Preference _pref;

        DeleteUserToProjectTask(Preference pref, Context context, int projectId)
        {
            _context = context;
            _projectId = projectId;
            _pref = pref;
        }

        private boolean handleAPIError(JSONObject infos) throws JSONException {
            if (!infos.getString("return_code").startsWith("1."))
            {
                AlertDialog.Builder builder = new AlertDialog.Builder(_context);

                if (infos.getString("return_code").equals("6.11.4"))
                    builder.setMessage(R.string.str_error_remove_project_creator);
                else
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
                return true;
            }
            return false;
        }

        @Override
        protected String doInBackground(String... params) {
            _api = APIConnectAdapter.getInstance(true);

            try {
                _api.setVersion("V0.2");
                _api.startConnection("projects/removeusertoproject/" + SessionAdapter.getInstance().getToken() + "/" + String.valueOf(_projectId) + "/" + String.valueOf(_user.getId()));
                _api.setRequestConnection("DELETE");
                return _api.getInputSream();
            } catch (IOException e) {
                e.printStackTrace();
            }

            return null;
        }

        @Override
        protected void onPostExecute(String s) {
            super.onPostExecute(s);

            JSONObject json, info, data;
            JSONArray array;
            if ( s == null ||s.isEmpty())
                return;
            try {
                json = new JSONObject(s);
                info = json.getJSONObject("info");
                if (disconnectAPI())
                    return;
                assert info != null;
                if (handleAPIError(info))
                    return;
                _parent.removePreference(_pref);
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
        }
    }

    public class AddUserToProjectTask extends AsyncTask<String, Void, String>
    {
        Context _context;
        APIConnectAdapter _api;
        int _projectId;
        TeamPreference _pref;

        AddUserToProjectTask(TeamPreference pref, Context context, int projectId)
        {
            _pref = pref;
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
                return true;
            }
            return false;
        }

        @Override
        protected String doInBackground(String... params) {
            if (params.length < 1)
                return null;
            String email = params[0];
            _api = APIConnectAdapter.getInstance(true);
            JSONObject json, data;

            assert email != null;
            json = new JSONObject();
            data = new JSONObject();
            try {
                data.put("token", SessionAdapter.getInstance().getToken());
                data.put("id", _projectId);
                data.put("email", email);
                json.put("data", data);
                _api.setVersion("V0.2");
                _api.startConnection("projects/addusertoproject");
                _api.setRequestConnection("POST");
                _api.sendJSON(json);
                return _api.getInputSream();
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            return null;
        }

        @Override
        protected void onPostExecute(String s) {
            super.onPostExecute(s);

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
                assert data != null;

                UserModel user = new UserModel(data);
                _pref.setUserModel(_projectId,_activity, user);
                TeamPreference addPref = new TeamPreference(_context, Xml.asAttributeSet(_context.getResources().getLayout(R.layout.dialog_add_team_member)));

                addPref.setUserModel(_projectId, _activity, new UserModel());
                addPref.setCategory(_parent);
                _parent.addPreference(addPref);
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
        }
    }
}
