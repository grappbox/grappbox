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
import android.graphics.drawable.ColorDrawable;
import android.graphics.drawable.Drawable;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Build;
import android.os.Bundle;
import android.preference.Preference;
import android.preference.PreferenceActivity;
import android.preference.PreferenceCategory;
import android.preference.PreferenceFragment;
import android.provider.MediaStore;
import android.support.v4.app.NavUtils;
import android.support.v4.content.ContextCompat;
import android.support.v4.content.res.ResourcesCompat;
import android.support.v7.app.ActionBar;
import android.util.Base64;
import android.util.Log;
import android.util.Xml;
import android.view.MenuItem;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.CustomerAccessModel;
import com.grappbox.grappbox.grappbox.Model.CustomerAccessPreference;
import com.grappbox.grappbox.grappbox.Model.ProjectModel;
import com.grappbox.grappbox.grappbox.Model.SafePasswordPreference;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.util.List;
import java.util.Objects;

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


    private static ProjectModel _modelBasicInfos;
    private static ProjectSettingsActivity _childrenParent;
    private static final String[] PreferenceKeys = {
            "project_name",
            "project_desc",
            "project_phone",
            "project_company_name",
            "project_contact_mail",
            "project_facebook_url",
            "project_twitter_url",
            "project_logo",
            "project_safe_password",
            "customer_zone"
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
            if (!super.onMenuItemSelected(featureId, item)) {
                NavUtils.navigateUpFromSameTask(this);
            }
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
                || GeneralPreferenceFragment.class.getName().equals(fragmentName);
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
                    if (prefKey.equals("customer_zone"))
                    {
                        RetreiveCustomersAccessesTask task = new RetreiveCustomersAccessesTask(_childrenParent, (PreferenceCategory) findPreference(prefKey));

                        task.execute();
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
}
