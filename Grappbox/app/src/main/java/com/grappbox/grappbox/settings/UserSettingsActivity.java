/*
 * Created by Marc Wieser on 28/10/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox.settings;


import android.annotation.TargetApi;
import android.content.Context;
import android.content.Intent;
import android.content.res.Configuration;
import android.database.Cursor;
import android.database.DatabaseUtils;
import android.os.Build;
import android.os.Bundle;
import android.preference.ListPreference;
import android.preference.Preference;
import android.preference.PreferenceFragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v7.app.AppCompatActivity;
import android.util.Pair;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.custom_preferences.DatePreference;
import com.grappbox.grappbox.custom_preferences.PasswordPreference;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

public class UserSettingsActivity extends AppCompatActivity {
    /**
     * A preference value change listener that updates the preference's summary
     * to reflect its new value.
     */
    private static Preference.OnPreferenceChangeListener sBindPreferenceSummaryToValueListener = new Preference.OnPreferenceChangeListener() {
        @Override
        public boolean onPreferenceChange(Preference preference, Object value) {
            String stringValue = value.toString();
            Bundle arg = new Bundle();
            if (preference instanceof ListPreference) {
                // For list preferences, look up the correct display value in
                // the preference's 'entries' list.
                ListPreference listPreference = (ListPreference) preference;
                int index = listPreference.findIndexOfValue(stringValue);

                // Set the summary to reflect the new value.
                preference.setSummary(
                        index >= 0
                                ? listPreference.getValue()
                                : null);
                arg.putString(preference.getKey(), stringValue);
            } else if (preference instanceof PasswordPreference){
                if (value instanceof String)
                    return true;
                Pair<String, String> castedVal = (Pair<String, String>) value;
                arg.putString("password", castedVal.second);
                arg.putString("oldPassword", castedVal.first);
            }
            else if (preference instanceof DatePreference){
                preference.setSummary(stringValue);
                arg.putString(preference.getKey(), stringValue);
            }
            else {
                // For all other preferences, set the summary to the value's
                // simple string representation.
                arg.putString(preference.getKey(), stringValue);
                preference.setSummary(stringValue);
            }
            if (!stringValue.isEmpty()){
                Intent update = new Intent(preference.getContext(), GrappboxJustInTimeService.class);
                update.setAction(GrappboxJustInTimeService.ACTION_UPDATE_USER_SETTINGS);
                update.putExtra(GrappboxJustInTimeService.EXTRA_BUNDLE, arg);
                preference.getContext().startService(update);
            }
            return true;
        }
    };

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
        sBindPreferenceSummaryToValueListener.onPreferenceChange(preference,"");
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_user_settings);
        setSupportActionBar((android.support.v7.widget.Toolbar) findViewById(R.id.toolbar));
        getSupportActionBar().setHomeButtonEnabled(true);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
    }



    /**
     * This fragment shows general preferences only. It is used when the
     * activity is showing a two-pane settings UI.
     */
    @TargetApi(Build.VERSION_CODES.HONEYCOMB)
    public static class GeneralPreferenceFragment extends PreferenceFragment implements LoaderManager.LoaderCallbacks<Cursor> {
        @Override
        public void onCreate(Bundle savedInstanceState) {
            super.onCreate(savedInstanceState);
            addPreferencesFromResource(R.xml.pref_general);
            setHasOptionsMenu(true);

            bindPreferenceSummaryToValue(findPreference("firstname"));
            bindPreferenceSummaryToValue(findPreference("lastname"));
            bindPreferenceSummaryToValue(findPreference("birthday"));
            bindPreferenceSummaryToValue(findPreference("phone"));
            bindPreferenceSummaryToValue(findPreference("country"));
            bindPreferenceSummaryToValue(findPreference("twitter"));
            bindPreferenceSummaryToValue(findPreference("linkedin"));
            bindPreferenceSummaryToValue(findPreference("password"));
        }

        @Override
        public void onActivityCreated(Bundle savedInstanceState) {
            super.onActivityCreated(savedInstanceState);
            if (getActivity() instanceof AppCompatActivity){
                ((AppCompatActivity) getActivity()).getSupportLoaderManager().initLoader(0, null, this);
            }
        }

        @Override
        public Loader<Cursor> onCreateLoader(int id, Bundle args) {
            return new CursorLoader(getActivity(), GrappboxContract.UserEntry.CONTENT_URI, null, GrappboxContract.UserEntry.COLUMN_CONTACT_EMAIL + "=?", new String[]{Session.getInstance(getActivity()).getCurrentAccount().name}, null);
        }

        @Override
        public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
            final String[] preferences = {"firstname", "lastname", "birthday", "phone", "country", "twitter", "linkedin"};
            DatabaseUtils.dumpCursor(data);
            if (data.moveToFirst())
                for (String pref : preferences){
                    findPreference(pref).setSummary(data.getString(data.getColumnIndex(Utils.Database.sUserApiDBMap.get(pref))));
                }
        }

        @Override
        public void onLoaderReset(Loader<Cursor> loader) {

        }
    }
}
