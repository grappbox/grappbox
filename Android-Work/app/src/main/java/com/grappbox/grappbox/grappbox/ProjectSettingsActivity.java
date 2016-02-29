package com.grappbox.grappbox.grappbox;


import android.annotation.TargetApi;
import android.app.Activity;
import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.res.Configuration;
import android.content.res.Resources;
import android.graphics.Bitmap;
import android.graphics.drawable.BitmapDrawable;
import android.graphics.drawable.Drawable;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Build;
import android.os.Bundle;
import android.preference.ListPreference;
import android.preference.Preference;
import android.preference.PreferenceActivity;
import android.preference.PreferenceCategory;
import android.preference.PreferenceFragment;
import android.provider.MediaStore;
import android.support.v7.app.ActionBar;
import android.util.Base64;
import android.util.Log;
import android.util.Xml;
import android.view.MenuItem;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.CustomerAccessModel;
import com.grappbox.grappbox.grappbox.Model.CustomerAccessPreference;
import com.grappbox.grappbox.grappbox.Model.ProjectModel;
import com.grappbox.grappbox.grappbox.Model.RoleModel;
import com.grappbox.grappbox.grappbox.Model.RoleModel.EAccess;
import com.grappbox.grappbox.grappbox.Model.RolePreference;
import com.grappbox.grappbox.grappbox.Model.SafePasswordPreference;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.Model.TeamPreference;
import com.grappbox.grappbox.grappbox.Model.UserModel;
import com.grappbox.grappbox.grappbox.Model.UserRolePreference;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;
import java.util.Objects;

import butterknife.internal.ListenerClass;
import rx.functions.Func0;
import rx.functions.Function;

/**
 * A {@link PreferenceActivity} that presents a set of application settings. On
 * handset devices, settings are presented as a single list. On tablets,
 * settings are split by category, with category headers shown to the left of
 * the list of settings.
 * <p>
 * See <a href="http://developer.android.com/design/patterns/settings.html">
 * Android Design: Settings</a> for design guidelines and the <a
 * href="http://developer.android.com/guide/topics/ui/settings.html">Settings
 * API Guide</a> for more information on developing a Settings UI.
 */
public class ProjectSettingsActivity extends AppCompatPreferenceActivity {
    private static int _projectId;

    public static final String EXTRA_PROJECT_ID = "ProjectSettingsActivity.extra.project_id";
    public static final String EXTRA_PROJECT_NAME = "ProjectSettingsActivity.extra.project_name";
    public static final String EXTRA_PROJECT_MODEL = "ProjectSettingsActivity.extra.project_model";
    public static final int PICK_PNG_FROM_SYSTEM = 21;
    private static boolean _isChildrenFragment = false;

    private static UserModel _currentUserSeen;
    private static RoleModel _currentRoleSeen;
    private static ProjectModel _modelBasicInfos;
    private static ProjectSettingsActivity _childrenParent;
    private static final String[] PreferenceKeys = {
            "project_name",
            "project_descproject_desc",
            "project_phone",
            "project_company_name",
            "project_contact_mail",
            "project_facebook_url",
            "project_twitter_url",
            "project_logo",
            "project_safe_password",
            "customer_zone",
            "project_team",
            "project_roles"
    };
    private GeneralPreferenceFragment _project_settings_fragment;

    private void fileSelectedResult(Intent data)
    {
        Uri uri = data.getData();

        try {
            Log.e("WATCH", uri.toString());
            Bitmap bitmap = MediaStore.Images.Media.getBitmap(this.getContentResolver(), uri);
            String result64;

            UpdateKeyBasicInfoTask task = new UpdateKeyBasicInfoTask(_childrenParent, _projectId);
            ByteArrayOutputStream byteArrayOutputStream = new ByteArrayOutputStream();
            bitmap.compress(Bitmap.CompressFormat.PNG, 100, byteArrayOutputStream);
            byte[] byteArray = byteArrayOutputStream .toByteArray();
            result64 = Base64.encodeToString(byteArray, Base64.DEFAULT);
            task.execute("project_logo", result64);
            Drawable drawable = new BitmapDrawable(getResources(), bitmap);
            _project_settings_fragment.findPreference("project_logo").setIcon(drawable);
        } catch (IOException e) {
            e.printStackTrace();
        }

    }

    public ProjectModel getModel()
    {
        return _modelBasicInfos;
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (resultCode != Activity.RESULT_OK)
            return;

        switch (requestCode)
        {
            case ProjectSettingsActivity.PICK_PNG_FROM_SYSTEM:
                fileSelectedResult(data);
                break;
            default:
                return;
        }
        super.onActivityResult(requestCode, resultCode, data);
    }

    public void setCurrentSeenUser(UserModel user)
    {
        _currentUserSeen = user;
    }

    public void setCurrentSeenRole(RoleModel role)
    {
        _currentRoleSeen = role;
    }


    /**
     * A preference value change listener that updates the preference's summary
     * to reflect its new value.
     */
    private static Preference.OnPreferenceChangeListener sBindPreferenceSummaryToValueListener = new Preference.OnPreferenceChangeListener() {
        @Override
        public boolean onPreferenceChange(Preference preference, Object value) {
            String stringValue = value.toString();
            if (stringValue.equals("") && _modelBasicInfos != null)
            {
                switch (preference.getKey())
                {
                    case "project_name":
                        preference.setSummary(_modelBasicInfos.getName());
                        preference.setDefaultValue(_modelBasicInfos.getName());
                        break;
                    case "project_desc":
                        preference.setSummary(_modelBasicInfos.getDescription());
                        preference.setDefaultValue(_modelBasicInfos.getDescription());
                        break;
                    case "project_phone":
                        preference.setSummary(_modelBasicInfos.getPhone());
                        preference.setDefaultValue(_modelBasicInfos.getPhone());
                        break;
                    case "project_company_name":
                        preference.setSummary(_modelBasicInfos.getCompany());
                        preference.setDefaultValue(_modelBasicInfos.getCompany());
                        break;
                    case "project_contact_mail":
                        preference.setSummary(_modelBasicInfos.getContact_mail());
                        preference.setDefaultValue(_modelBasicInfos.getContact_mail());
                        break;
                    case "project_facebook_url":
                        preference.setSummary(_modelBasicInfos.getFacebookURL());
                        preference.setDefaultValue(_modelBasicInfos.getFacebookURL());
                        break;
                    case "project_twitter_url":
                        preference.setSummary(_modelBasicInfos.getTwitterURL());
                        preference.setDefaultValue(_modelBasicInfos.getTwitterURL());
                        break;
                    case "project_logo":
                        Drawable icon = new BitmapDrawable(Resources.getSystem(), _modelBasicInfos.getLogo(_childrenParent.getApplicationContext()));

                        if (_modelBasicInfos.getLogo(_childrenParent.getApplicationContext()) == null)
                            preference.setIcon(R.mipmap.icon_launcher);
                        else
                            preference.setIcon(icon);
                        break;
                    default:
                        break;
                }
                return true;
            }
            else {
                // For all other preferences, set the summary to the value's
                // simple string representation.
                if (!preference.getKey().equals("project_logo"))
                {
                    UpdateKeyBasicInfoTask task = new UpdateKeyBasicInfoTask(_childrenParent, _projectId);

                    task.execute(preference.getKey(), stringValue);
                    preference.setSummary(stringValue);
                }
                else
                {
                    Intent intent = new Intent(Intent.ACTION_GET_CONTENT);
                    intent.setType("image/png");
                    intent.addCategory(Intent.CATEGORY_OPENABLE);
                    _childrenParent.startActivityForResult(intent, ProjectSettingsActivity.PICK_PNG_FROM_SYSTEM);
                }
            }
            return true;
        }
    };

    private static Preference.OnPreferenceClickListener sBindPreferenceToRetreiveProjectListener = new Preference.OnPreferenceClickListener() {
        @Override
        public boolean onPreferenceClick(Preference preference) {
            RetreiveProjectTask task = new RetreiveProjectTask(_childrenParent.getBaseContext());
            task.execute();
            return false;
        }
    };

    private static Preference.OnPreferenceClickListener sBindPreferenceToDeleteProjectListener = new Preference.OnPreferenceClickListener() {
        @Override
        public boolean onPreferenceClick(Preference preference) {
            DeleteProjectTask task = new DeleteProjectTask(_childrenParent.getBaseContext());
            task.execute();
            return false;
        }
    };

    public void setPreferencesEnabled(boolean enabled)
    {
        for (String prefKey : PreferenceKeys)
            _project_settings_fragment.findPreference(prefKey).setEnabled(enabled);
    }

    public GeneralPreferenceFragment getProjectSettingsFragment()
    {
        return _project_settings_fragment;
    }


    private static boolean isXLargeTablet(Context context) {
        return (context.getResources().getConfiguration().screenLayout
                & Configuration.SCREENLAYOUT_SIZE_MASK) >= Configuration.SCREENLAYOUT_SIZE_XLARGE;
    }

    private static void bindPreferenceSummaryToValue(Preference preference) {
        // Set the listener to watch for value changes.
        if (preference == null)
            return;
        preference.setOnPreferenceChangeListener(sBindPreferenceSummaryToValueListener);

        // Trigger the listener immediately with the preference's
        // current value.
        sBindPreferenceSummaryToValueListener.onPreferenceChange(preference, "");
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        Intent intent = getIntent();
        String title;

        assert (intent != null);
        _childrenParent = this;
        _projectId = intent.getIntExtra(EXTRA_PROJECT_ID, 0);
        title = intent.getStringExtra(EXTRA_PROJECT_NAME);

        setTitle(title == null ? getString(R.string.str_project_settings_title) : title + getString(R.string.str_project_settings_footer));
        _modelBasicInfos = (ProjectModel) intent.getSerializableExtra(EXTRA_PROJECT_MODEL);

        super.onCreate(savedInstanceState);
        setupActionBar();
    }

    private void setupActionBar() {
        ActionBar actionBar = getSupportActionBar();
        if (actionBar != null) {
            // Show the Up button in the action bar.
            actionBar.setDisplayHomeAsUpEnabled(true);
        }
    }

    @Override
    public boolean onMenuItemSelected(int featureId, MenuItem item) {
        int id = item.getItemId();

        if (id == android.R.id.home) {
            if (_isChildrenFragment) {
                onBackPressed();
                _isChildrenFragment = false;
            }
            onBackPressed();
            return true;
        }
        return super.onMenuItemSelected(featureId, item);
    }

    @Override
    public boolean onIsMultiPane() {
        return isXLargeTablet(this);
    }

    @Override
    @TargetApi(Build.VERSION_CODES.HONEYCOMB)
    public void onBuildHeaders(List<Header> target) {
        loadHeadersFromResource(R.xml.pref_headers, target);
    }

    protected boolean isValidFragment(String fragmentName) {
        return PreferenceFragment.class.getName().equals(fragmentName)
                || GeneralPreferenceFragment.class.getName().equals(fragmentName)
                || TeamPreferenceFragment.class.getName().equals(fragmentName)
                || RolePreferenceFragment.class.getName().equals(fragmentName);
    }

    public void setProjectPreferenceFragment(GeneralPreferenceFragment frag)
    {
        _project_settings_fragment = frag;
    }

    @TargetApi(Build.VERSION_CODES.HONEYCOMB)
    public static class GeneralPreferenceFragment extends PreferenceFragment {
        static GeneralPreferenceFragment _baseFragment;
        @Override
        public void onCreate(Bundle savedInstanceState) {
            super.onCreate(savedInstanceState);
            addPreferencesFromResource(R.xml.pref_general);
            setHasOptionsMenu(true);
            getActivity().setTitle(_modelBasicInfos.getName() + getActivity().getString(R.string.str_project_settings_footer));
            _isChildrenFragment = false;
            _childrenParent.setProjectPreferenceFragment(this);
            _baseFragment = this;
            // Bind the summaries of EditText/List/Dialog/Ringtone preferences
            // to their values. When their values change, their summaries are
            // updated to reflect the new value, per the Android Design
            // guidelines.
            for (String prefKey : PreferenceKeys) {
                if (_modelBasicInfos.isDeleted())
                {
                    findPreference(prefKey).setEnabled(false);
                    switch (prefKey)
                    {
                        case "customer_zone":
                            RetreiveCustomersAccessesTask task = new RetreiveCustomersAccessesTask(_childrenParent, (PreferenceCategory) findPreference(prefKey));
                            task.execute();
                            break;
                        case "project_team":
                            RetreiveTeamInfoTask teamTask = new RetreiveTeamInfoTask((PreferenceCategory) findPreference(prefKey), _childrenParent, _projectId);
                            teamTask.execute();
                            break;
                        case "project_roles":
                            RetreiveProjectRoles rolesTask = new RetreiveProjectRoles(_childrenParent, _projectId, null, this);

                            rolesTask.execute();
                            break;
                        default:
                            break;
                    }
                    continue;
                }
                if (prefKey.equals("project_safe_password"))
                    ((SafePasswordPreference)findPreference(prefKey)).setProjectId(_projectId);
                super.getActivity().getSharedPreferences("", Context.MODE_PRIVATE).edit().remove(prefKey).commit();
                bindPreferenceSummaryToValue(findPreference(prefKey));
                if (Objects.equals(prefKey, "project_logo"))
                    findPreference(prefKey).setOnPreferenceClickListener(new Preference.OnPreferenceClickListener() {
                        @Override
                        public boolean onPreferenceClick(Preference preference) {
                            Intent intent = new Intent(Intent.ACTION_GET_CONTENT);
                            intent.setType("image/png");
                            intent.addCategory(Intent.CATEGORY_OPENABLE);
                            _childrenParent.startActivityForResult(intent, ProjectSettingsActivity.PICK_PNG_FROM_SYSTEM);
                            return false;
                        }
                    });
                else if (prefKey.equals("customer_zone"))
                {
                    RetreiveCustomersAccessesTask task = new RetreiveCustomersAccessesTask(_childrenParent, (PreferenceCategory) findPreference(prefKey));

                    task.execute();
                }
                else if (prefKey.equals("project_team"))
                {
                    RetreiveTeamInfoTask teamTask = new RetreiveTeamInfoTask((PreferenceCategory) findPreference(prefKey), _childrenParent, _projectId);
                    teamTask.execute();
                }
                else if (prefKey.equals("project_roles"))
                {
                    RetreiveProjectRoles rolesTask = new RetreiveProjectRoles(_childrenParent, _projectId, null, this);

                    rolesTask.execute();
                }
            }
            if (_modelBasicInfos.isDeleted())
            {
                Preference pref = findPreference("project_delete");

                pref.setSummary("");
                pref.setTitle(R.string.str_retreive_project);
                pref.setOnPreferenceClickListener(sBindPreferenceToRetreiveProjectListener);
            }
            else
            {
                Preference pref = findPreference("project_delete");

                pref.setOnPreferenceClickListener(sBindPreferenceToDeleteProjectListener);
            }
        }

        @Override
        public boolean onOptionsItemSelected(MenuItem item) {
            int id = item.getItemId();
            if (id == android.R.id.home) {
                startActivity(new Intent(getActivity(), MainActivity.class));
                return true;
            }
            return super.onOptionsItemSelected(item);
        }
    }



    public static class UpdateKeyBasicInfoTask extends AsyncTask<String, Void, String>
    {
        APIConnectAdapter _api;
        Activity _context;
        int      _projectId;

        UpdateKeyBasicInfoTask(Activity context, int projectId)
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
            if (params.length < 2)
                return null;
            String key = params[0];
            String value = params[1];
            String jsonKey;
            JSONObject json, data;

            _api = APIConnectAdapter.getInstance(true);
            data = new JSONObject();
            json = new JSONObject();
            try {
                data.put("token", SessionAdapter.getInstance().getToken());
                data.put("projectId", _projectId);
                switch(key)
                {
                    case "project_name":
                        jsonKey = "name";
                        break;
                    case "project_desc":
                        jsonKey = "description";
                        break;
                    case "project_phone":
                        jsonKey = "phone";
                        break;
                    case "project_company_name":
                        jsonKey = "company";
                        break;
                    case "project_contact_mail":
                        jsonKey = "email";
                        break;
                    case "project_facebook_url":
                        jsonKey = "facebook";
                        break;
                    case "project_twitter_url":
                        jsonKey = "twitter";
                        break;
                    case "project_logo":
                        jsonKey = "logo";
                        break;
                    case "project_safe_password":
                        jsonKey = "password";
                    default:
                        Log.e("WATCHME", "DEFAULT SWITCH");
                        return null;
                }
                data.put(jsonKey, value);
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

    public static class DeleteProjectTask extends AsyncTask<String, Void, String>
    {
        Context _context;
        APIConnectAdapter _api;

        DeleteProjectTask(Context context)
        {
            _context = context;
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
                _childrenParent.setPreferencesEnabled(false);
                Preference deletePref = _childrenParent._project_settings_fragment.findPreference("project_delete");
                deletePref.setSummary("");
                deletePref.setTitle(R.string.str_retreive_project);
                deletePref.setOnPreferenceClickListener(sBindPreferenceToRetreiveProjectListener);
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            super.onPostExecute(s);
        }

        @Override
        protected String doInBackground(String... params) {
            _api = APIConnectAdapter.getInstance(true);

            try {
                _api.setVersion("V0.2");
                _api.startConnection("projects/delproject/" + SessionAdapter.getInstance().getToken() + "/" + String.valueOf(_projectId));
                _api.setRequestConnection("DELETE");
                return _api.getInputSream();
            } catch (IOException e) {
                e.printStackTrace();
            }
            return null;
        }
    }

    public static class RetreiveProjectTask extends AsyncTask<String, Void, String>
    {
        Context _context;
        APIConnectAdapter _api;

        RetreiveProjectTask(Context context)
        {
            _context = context;
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
                _childrenParent.setPreferencesEnabled(true);
                Preference deletePref = _childrenParent._project_settings_fragment.findPreference("project_delete");
                deletePref.setSummary(R.string.str_retreive_explaination);
                deletePref.setTitle(R.string.str_project_delete);
                deletePref.setOnPreferenceClickListener(sBindPreferenceToDeleteProjectListener);
                _modelBasicInfos.setDeletedAt(null);
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            super.onPostExecute(s);
        }

        @Override
        protected String doInBackground(String... params) {

            _api = APIConnectAdapter.getInstance(true);
            try {
                _api.setVersion("V0.2");
                _api.startConnection("projects/retrieveproject/" + SessionAdapter.getInstance().getToken() + "/" + _projectId);
                _api.setRequestConnection("GET");
                return _api.getInputSream();
            } catch (IOException e) {
                e.printStackTrace();
            }
            return null;
        }
    }

    public static class RetreiveCustomersAccessesTask extends AsyncTask<String, Void, String>
    {
        PreferenceCategory _customer_zone;
        Context _context;
        APIConnectAdapter _api;

        RetreiveCustomersAccessesTask(Context context, PreferenceCategory customer_zone)
        {
            _context = context;
            _customer_zone = customer_zone;
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
        protected void onPostExecute(String s) {
            assert s != null;
            JSONObject json, info, data;
            JSONArray array;

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
                array = data.getJSONArray("array");
                assert array != null;
                for (int i = 0; i < array.length(); ++i)
                {
                    JSONObject current = array.getJSONObject(i);
                    CustomerAccessPreference customerAccess = new CustomerAccessPreference(_context, Xml.asAttributeSet(_context.getResources().getLayout(R.layout.dialog_customer_access_pref)));
                    customerAccess.setProjectId(_projectId);
                    assert current != null;
                    customerAccess.setCustomerAccess(new CustomerAccessModel(current));
                    customerAccess.setCustomerZone(_customer_zone);
                    _customer_zone.addPreference(customerAccess);
                }
                CustomerAccessPreference creator = new CustomerAccessPreference(_context, Xml.asAttributeSet(_context.getResources().getLayout(R.layout.dialog_customer_access_pref)));

                creator.setCustomerAccess(new CustomerAccessModel());
                creator.setProjectId(_projectId);
                creator.setCustomerZone(_customer_zone);
                _customer_zone.addPreference(creator);

            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            super.onPostExecute(s);
        }

        @Override
        protected String doInBackground(String... params) {
            _api = APIConnectAdapter.getInstance(true);
            try {
                _api.setVersion("V0.2");
                _api.startConnection("projects/getcustomeraccessbyproject/" + SessionAdapter.getInstance().getToken() + "/" + _projectId);
                _api.setRequestConnection("GET");
                return _api.getInputSream();
            } catch (IOException e) {
                e.printStackTrace();
            }
            return null;
        }
    }

    public static class RetreiveTeamInfoTask extends AsyncTask<String, Void, String>
    {
        Context _context;
        APIConnectAdapter _api;
        int _projectId;
        PreferenceCategory _category;

        RetreiveTeamInfoTask(PreferenceCategory category, Context context, int projectId)
        {
            _context = context;
            _projectId = projectId;
            _category = category;
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
        protected void onPostExecute(String s) {

            assert s != null;
            JSONObject json, info, data;
            JSONArray array;

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
                array = data.getJSONArray("array");
                assert array != null;
                for (int i = 0; i < array.length(); ++i)
                {
                    JSONObject obj = array.getJSONObject(i);

                    assert obj != null;
                    UserModel user = new UserModel(obj);
                    TeamPreference newPref = new TeamPreference(_context, Xml.asAttributeSet(_context.getResources().getLayout(R.layout.dialog_manage_team_member)));
                    newPref.setUserModel(_projectId, _childrenParent, user);
                    newPref.setCategory(_category);
                    _category.addPreference(newPref);
                }
                UserModel addUser = new UserModel();
                TeamPreference addPref = new TeamPreference(_context, Xml.asAttributeSet(_context.getResources().getLayout(R.layout.dialog_add_team_member)));
                addPref.setUserModel(_projectId, _childrenParent, addUser);
                addPref.setCategory(_category);
                _category.addPreference(addPref);
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            super.onPostExecute(s);
        }

        @Override
        protected String doInBackground(String... params) {
            _api = APIConnectAdapter.getInstance(true);

            try {
                _api.setVersion("V0.2");
                _api.startConnection("projects/getusertoproject/" + SessionAdapter.getInstance().getToken() + "/" + String.valueOf(_projectId));
                _api.setRequestConnection("GET");
                return _api.getInputSream();
            } catch (IOException e) {
                e.printStackTrace();
            }
            return null;
        }
    }

    //--------------------------- TEAM FRAGMENT -------------------------//

    public static class TeamPreferenceFragment extends PreferenceFragment
    {
        @Override
        public void onCreate(Bundle savedInstanceState) {
            super.onCreate(savedInstanceState);
            addPreferencesFromResource(R.xml.pref_team_member);
            _isChildrenFragment = true;
            RetreiveRolesIDUserProjectTask task = new RetreiveRolesIDUserProjectTask(_childrenParent, _projectId, this);
            task.execute();
            getActivity().setTitle(_currentUserSeen.getCompleteName());
        }

    }

    public static class RetreiveRolesIDUserProjectTask extends AsyncTask<String, Void, String> {
        APIConnectAdapter _api;
        Context _context;
        int _projectId;
        PreferenceFragment _frag;

        RetreiveRolesIDUserProjectTask(Context context, int projectID, PreferenceFragment fragment) {
            _context = context;
            _projectId = projectID;
            _frag = fragment;
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
            assert _currentUserSeen != null;
            _api = APIConnectAdapter.getInstance(true);

            _api.setVersion("V0.2");
            try {
                _api.startConnection("roles/getrolebyprojectanduser/" + SessionAdapter.getInstance().getToken() + "/" + String.valueOf(_projectId) + "/" + _currentUserSeen.getId());
                _api.setRequestConnection("GET");
                return _api.getInputSream();
            } catch (IOException e) {
                e.printStackTrace();
            }

            return null;
        }

        @Override
        protected void onPostExecute(String s) {
            assert s != null;
            JSONObject json, info, data;
            JSONArray array;

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
                array = data.getJSONArray("array");
                ArrayList<Integer> ids = new ArrayList<>();
                for (int i = 0; i < array.length(); ++i)
                {
                    JSONObject obj = array.getJSONObject(i);
                    assert obj != null;
                    ids.add(obj.getInt("id"));
                }
                RetreiveProjectRoles task = new RetreiveProjectRoles(_context, _projectId, ids, _frag);

                task.execute();
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            super.onPostExecute(s);
        }
    }

    public static class RetreiveProjectRoles extends AsyncTask<String, Void, String> {
        APIConnectAdapter _api;
        Context _context;
        int _projectId;
        ArrayList<Integer> _ids;
        PreferenceFragment _frag;

        RetreiveProjectRoles(Context context, int projectID, ArrayList<Integer> ids, PreferenceFragment fragment) {
            _context = context;
            _projectId = projectID;
            _ids = ids;
            _frag = fragment;
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
                _api.startConnection("roles/getprojectroles/" + SessionAdapter.getInstance().getToken() + "/" + String.valueOf(_projectId));
                _api.setRequestConnection("GET");
                return _api.getInputSream();
            } catch (IOException e) {
                e.printStackTrace();
            }
            return null;
        }

        @Override
        protected void onPostExecute(String s) {
            JSONObject json, info, data;
            JSONArray array;

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
                array = data.getJSONArray("array");
                assert array != null;
                for (int i = 0; i < array.length(); ++i)
                {
                    JSONObject obj = array.getJSONObject(i);
                    assert obj != null;
                    RoleModel model = new RoleModel(obj);
                    if (_ids != null) {
                        UserRolePreference pref = new UserRolePreference(_context);
                        pref.setRoleModel(model, _ids.contains(model.getId()), _projectId, _currentUserSeen.getId());
                        _frag.getPreferenceScreen().addPreference(pref);
                    }
                    else
                    {
                        RolePreference pref = new RolePreference(_context,Xml.asAttributeSet(_context.getResources().getLayout(R.layout.dialog_role_choices)));

                        pref.initalize((PreferenceCategory)_frag.findPreference("project_roles"), model, _projectId, _childrenParent);
                        ((PreferenceCategory)_frag.getPreferenceScreen().findPreference("project_roles")).addPreference(pref);
                    }
                }
                if (_ids != null)
                    _frag.getPreferenceScreen().removePreference(_frag.findPreference("team_member_loading"));
                else
                {
                    RolePreference pref = new RolePreference(_context,Xml.asAttributeSet(_context.getResources().getLayout(R.layout.dialog_role_choices)));

                    pref.initalize((PreferenceCategory)_frag.findPreference("project_roles"), new RoleModel(), _projectId, _childrenParent);
                    ((PreferenceCategory)_frag.getPreferenceScreen().findPreference("project_roles")).addPreference(pref);
                }
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            super.onPostExecute(s);
        }
    }

    //--------------------------- ROLE FRAGMENT -------------------------//
    public static class RolePreferenceFragment extends PreferenceFragment
    {
        private static final String[] keys = {
                "role_team_timeline",
                "role_customer_timeline",
                "role_gantt",
                "role_whiteboard",
                "role_bugtracker",
                "role_event",
                "role_task",
                "role_project_settings",
                "role_cloud"
        };

        public void setRoleAccess(String key, int value)
        {
            EAccess access = EAccess.NONE;
            if (value > 0)
                access = (value == 1 ? EAccess.READ : EAccess.READ_WRITE);
            switch (key)
            {
                case "role_team_timeline":
                    _currentRoleSeen.setTeam_timeline(access);
                    break;
                case "role_customer_timeline":
                    _currentRoleSeen.setCustomer_timeline(access);
                    break;
                case "role_gantt":
                    _currentRoleSeen.setGantt(access);
                    break;
                case "role_whiteboard":
                    _currentRoleSeen.setWhiteboard(access);
                    break;
                case "role_bugtracker":
                    _currentRoleSeen.setBugtracker(access);
                    break;
                case "role_event":
                    _currentRoleSeen.setEvent(access);
                    break;
                case "role_task":
                    _currentRoleSeen.setTask(access);
                    break;
                case "role_project_settings":
                    _currentRoleSeen.setProject_settings(access);
                    break;
                case "role_cloud":
                    _currentRoleSeen.setCloud(access);
                    break;
                default:
                    break;
            }
        }

        public EAccess getRoleAccess(String key)
        {
            switch (key)
            {
                case "role_team_timeline":
                    return _currentRoleSeen.getTeam_timeline();
                case "role_customer_timeline":
                    return _currentRoleSeen.getCustomer_timeline();
                case "role_gantt":
                    return _currentRoleSeen.getGantt();
                case "role_whiteboard":
                    return _currentRoleSeen.getWhiteboard();
                case "role_bugtracker":
                    return _currentRoleSeen.getBugtracker();
                case "role_event":
                    return _currentRoleSeen.getEvent();
                case "role_task":
                    return _currentRoleSeen.getTask();
                case "role_project_settings":
                    return _currentRoleSeen.getProject_settings();
                case "role_cloud":
                    return _currentRoleSeen.getCloud();
                default:
                    return EAccess.NONE;
            }
        }

        public String getRoleKey(String prefkey)
        {
            switch (prefkey)
            {
                case "role_team_timeline":
                    return "teamTimeline";
                case "role_customer_timeline":
                    return "customerTimeline";
                case "role_gantt":
                    return "gantt";
                case "role_whiteboard":
                    return "whiteboard";
                case "role_bugtracker":
                    return "bugtracker";
                case "role_event":
                    return "event";
                case "role_task":
                    return "task";
                case "role_project_settings":
                    return "projectSettings";
                case "role_cloud":
                    return "cloud";
                default:
                    return null;
            }
        }

        @Override
        public void onCreate(Bundle savedInstanceState) {
            super.onCreate(savedInstanceState);
            RolePreferenceFragment fragment = this;
            addPreferencesFromResource(R.xml.pref_role_edit);
            _isChildrenFragment = true;

            getActivity().setTitle(_currentRoleSeen.getName());
            for (String key : keys)
            {
                ListPreference list = (ListPreference) findPreference(key);

                list.setEntries(R.array.list_roles_opt_string);
                list.setEntryValues(R.array.list_roles_opt);
                EAccess access = getRoleAccess(key);
                list.setValueIndex(getRoleAccess(key).toInt());
                list.setSummary(getResources().getStringArray(R.array.list_roles_opt_string)[access.toInt()]);
                list.setOnPreferenceChangeListener(new Preference.OnPreferenceChangeListener() {
                    @Override
                    public boolean onPreferenceChange(Preference preference, Object newValue) {
                        String value = (String) newValue;
                        int newAccess;
                        switch (value)
                        {
                            case "no_access":
                                newAccess = EAccess.NONE.toInt();
                                break;
                            case "read_only":
                                newAccess = EAccess.READ.toInt();
                                break;
                            case "read_write":
                                newAccess = EAccess.READ_WRITE.toInt();
                                break;
                            default:
                                return false;
                        }
                        EditRoleTask editTask = new EditRoleTask(getActivity(), newAccess, fragment, list);

                        editTask.execute(getRoleKey(key));
                        list.setSummary(getResources().getStringArray(R.array.list_roles_opt_string)[newAccess]);
                        list.setValueIndex(newAccess);
                        return false;
                    }
                });
            }
        }
    }

    public static class EditRoleTask extends AsyncTask<String, Void, String>
    {
        Context _context;
        int _roleAccess;
        int _lastAccess;
        APIConnectAdapter _api;
        RolePreferenceFragment _frag;
        Preference _pref;
        String _key;

        EditRoleTask(Context context, int access, RolePreferenceFragment frag, Preference pref)
        {
            _context = context;
            _roleAccess = access;
            _frag = frag;
            _pref = pref;
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
            String key = params[0];
            JSONObject json, data;

            _api = APIConnectAdapter.getInstance(true);
            _api.setVersion("V0.2");
            json = new JSONObject();
            data = new JSONObject();
            try {
                data.put("token", SessionAdapter.getInstance().getToken());
                data.put("roleId", _currentRoleSeen.getId());
                data.put(key, _roleAccess);
                json.put("data", data);
                _api.startConnection("roles/putprojectroles");
                _api.setRequestConnection("PUT");
                _lastAccess = _frag.getRoleAccess(key).toInt();
                _api.sendJSON(json);
                _key = key;
                return _api.getInputSream();
            } catch (IOException | JSONException e) {
                e.printStackTrace();
            }

            return null;
        }

        @Override
        protected void onPostExecute(String s) {
            JSONObject json, info, data;
            JSONArray array;

            if (s == null || s.isEmpty()) {
                _pref.setSummary(_context.getResources().getStringArray(R.array.dialog_roles_choice_opt_str)[_lastAccess]);
                ((ListPreference)_pref).setValueIndex(_lastAccess);
                return;
            }
            try {
                json = new JSONObject(s);
                info = json.getJSONObject("info");
                if (disconnectAPI())
                {
                    _pref.setSummary(_context.getResources().getStringArray(R.array.dialog_roles_choice_opt_str)[_lastAccess]);
                    ((ListPreference)_pref).setValueIndex(_lastAccess);
                    return;
                }
                assert info != null;
                if (handleAPIError(info)) {
                    _pref.setSummary(_context.getResources().getStringArray(R.array.dialog_roles_choice_opt_str)[_lastAccess]);
                    ((ListPreference)_pref).setValueIndex(_lastAccess);
                    return;
                }
                _frag.setRoleAccess(_key, _roleAccess);
            } catch (JSONException | IOException e) {
                e.printStackTrace();
            }
            super.onPostExecute(s);
        }
    }
}
