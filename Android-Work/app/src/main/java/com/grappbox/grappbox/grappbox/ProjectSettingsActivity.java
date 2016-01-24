package com.grappbox.grappbox.grappbox;


import android.annotation.TargetApi;
import android.app.Application;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.res.Configuration;
import android.content.res.Resources;
import android.graphics.drawable.BitmapDrawable;
import android.graphics.drawable.Drawable;
import android.media.Ringtone;
import android.media.RingtoneManager;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.preference.ListPreference;
import android.preference.Preference;
import android.preference.PreferenceActivity;
import android.support.v4.content.res.ResourcesCompat;
import android.support.v7.app.ActionBar;
import android.preference.PreferenceFragment;
import android.preference.PreferenceManager;
import android.preference.RingtonePreference;
import android.text.TextUtils;
import android.view.MenuItem;
import android.support.v4.app.NavUtils;

import com.grappbox.grappbox.grappbox.Model.ProjectModel;

import java.util.List;

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
    private int _projectId;
    public static final String EXTRA_PROJECT_ID = "ProjectSettingsActivity.extra.project_id";
    public static final String EXTRA_PROJECT_NAME = "ProjectSettingsActivity.extra.project_name";
    public static final String EXTRA_PROJECT_MODEL = "ProjectSettingsActivity.extra.project_model";
    private static ProjectModel _modelBasicInfos;
    private static final String[] PreferenceKeys = {
            "project_name",
            "project_desc",
            "project_phone",
            "project_company_name",
            "project_contact_mail",
            "project_facebook_url",
            "project_twitter_url",
            "project_logo"
    };

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
                        Drawable icon = new BitmapDrawable(Resources.getSystem(), _modelBasicInfos.getLogo());

                        if (_modelBasicInfos.getLogo() == null)
                            preference.setIcon(R.mipmap.icon_launcher);
                        else
                            preference.setIcon(icon);
                        break;
                    default:
                        break;
                }
                return true;
            }
            if (preference instanceof ListPreference) {
                // For list preferences, look up the correct display value in
                // the preference's 'entries' list.
                ListPreference listPreference = (ListPreference) preference;
                int index = listPreference.findIndexOfValue(stringValue);

                // Set the summary to reflect the new value.
                preference.setSummary(
                        index >= 0
                                ? listPreference.getEntries()[index]
                                : null);

            } else {
                // For all other preferences, set the summary to the value's
                // simple string representation.
                preference.setSummary(stringValue);
            }
            return true;
        }
    };

    /**
     * Helper method to determine if the device has an extra-large screen. For
     * example, 10" tablets are extra-large.
     */
    private static boolean isXLargeTablet(Context context) {
        return (context.getResources().getConfiguration().screenLayout
                & Configuration.SCREENLAYOUT_SIZE_MASK) >= Configuration.SCREENLAYOUT_SIZE_XLARGE;
    }

    /**
     * Binds a preference's summary to its value. More specifically, when the
     * preference's value is changed, its summary (line of text below the
     * preference title) is updated to reflect the value. The summary is also
     * immediately updated upon calling this method. The exact display format is
     * dependent on the type of preference.
     *
     * @see #sBindPreferenceSummaryToValueListener
     */
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

        _projectId = intent.getIntExtra(EXTRA_PROJECT_ID, 0);
        title = intent.getStringExtra(EXTRA_PROJECT_NAME);

        setTitle(title == null ? getString(R.string.str_project_settings_title) : title + getString(R.string.str_project_settings_footer));
        _modelBasicInfos = (ProjectModel) intent.getSerializableExtra(EXTRA_PROJECT_MODEL);

        super.onCreate(savedInstanceState);
        setupActionBar();
    }

    /**
     * Set up the {@link android.app.ActionBar}, if the API is available.
     */
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

    /**
     * {@inheritDoc}
     */
    @Override
    public boolean onIsMultiPane() {
        return isXLargeTablet(this);
    }

    /**
     * {@inheritDoc}
     */
    @Override
    @TargetApi(Build.VERSION_CODES.HONEYCOMB)
    public void onBuildHeaders(List<Header> target) {
        loadHeadersFromResource(R.xml.pref_headers, target);
    }

    /**
     * This method stops fragment injection in malicious applications.
     * Make sure to deny any unknown fragments here.
     */
    protected boolean isValidFragment(String fragmentName) {
        return PreferenceFragment.class.getName().equals(fragmentName)
                || GeneralPreferenceFragment.class.getName().equals(fragmentName);
    }

    /**
     * This fragment shows general preferences only. It is used when the
     * activity is showing a two-pane settings UI.
     */
    @TargetApi(Build.VERSION_CODES.HONEYCOMB)
    public static class GeneralPreferenceFragment extends PreferenceFragment {
        @Override
        public void onCreate(Bundle savedInstanceState) {
            super.onCreate(savedInstanceState);
            addPreferencesFromResource(R.xml.pref_general);
            setHasOptionsMenu(true);

            // Bind the summaries of EditText/List/Dialog/Ringtone preferences
            // to their values. When their values change, their summaries are
            // updated to reflect the new value, per the Android Design
            // guidelines.
            for (String prefKey : PreferenceKeys) {
                super.getActivity().getSharedPreferences("", Context.MODE_PRIVATE).edit().remove(prefKey).commit();
                bindPreferenceSummaryToValue(findPreference(prefKey));
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
}
