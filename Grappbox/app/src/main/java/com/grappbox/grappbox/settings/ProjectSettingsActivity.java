/*
 * Created by Marc Wieser on 29/10/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox.settings;


import android.annotation.SuppressLint;
import android.annotation.TargetApi;
import android.content.Context;
import android.content.Intent;
import android.content.res.Configuration;
import android.database.Cursor;
import android.database.DatabaseUtils;
import android.media.Ringtone;
import android.media.RingtoneManager;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.preference.ListPreference;
import android.preference.Preference;
import android.preference.PreferenceActivity;
import android.app.ActionBar;
import android.preference.PreferenceCategory;
import android.preference.PreferenceFragment;
import android.preference.PreferenceManager;
import android.preference.PreferenceScreen;
import android.preference.RingtonePreference;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentPagerAdapter;
import android.support.v4.app.FragmentStatePagerAdapter;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v4.view.PagerAdapter;
import android.support.v4.view.ViewPager;
import android.support.v7.app.AppCompatActivity;
import android.text.TextUtils;
import android.util.AttributeSet;
import android.util.Log;
import android.util.Pair;
import android.view.LayoutInflater;
import android.view.MenuItem;
import android.support.v4.app.NavUtils;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.custom_preferences.CustomerAccessPreference;
import com.grappbox.grappbox.custom_preferences.PasswordPreference;
import com.grappbox.grappbox.custom_preferences.UserPreference;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.data.GrappboxContract.CustomerAccessEntry;
import com.grappbox.grappbox.model.CustomerAccessModel;
import com.grappbox.grappbox.model.UserModel;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import java.util.List;

public class ProjectSettingsActivity extends AppCompatActivity {
    private static Intent activityIntent = null;
    private static final int LOADER_GENERAL_SETTINGS = 0;
    private static final int LOADER_USER_LIST = 1;
    private static final int LOADER_ROLES_LIST = 2;
    private static final int LOADER_CUSTOMER_ACCESS = 3;
    private static final int LOADER_ROLES_ACCESS = 4;

    @Override
    public View onCreateView(View parent, String name, Context context, AttributeSet attrs) {
        activityIntent = getIntent();
        return super.onCreateView(parent, name, context, attrs);
    }

    private static Preference.OnPreferenceChangeListener sBindPreferenceSummaryToValueListenerGeneral = new Preference.OnPreferenceChangeListener() {
        @SuppressWarnings("unchecked cast")
        @Override
        public boolean onPreferenceChange(Preference preference, Object value) {
            String stringValue = value.toString();
            if (activityIntent == null || stringValue.isEmpty())
                return true;
            Intent update = new Intent(preference.getContext(), GrappboxJustInTimeService.class);
            update.setAction(GrappboxJustInTimeService.ACTION_UPDATE_PROJECT_SETTINGS);
            Bundle arg = new Bundle();
            if (preference instanceof PasswordPreference) {
                if (value instanceof String)
                    return true;
                Pair<String, String> castedVal = (Pair<String, String>) value;
                arg.putString("password", castedVal.second);
                arg.putString("oldPassword", castedVal.first);
            } else {
                arg.putString(preference.getKey(), stringValue);
            }
            update.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, activityIntent.getLongExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, -1));
            update.putExtra(GrappboxJustInTimeService.EXTRA_BUNDLE, arg);
            preference.getContext().startService(update);
            return true;
        }
    };

    private static Preference.OnPreferenceChangeListener sBindPreferenceSummaryToValueListenerUsers = new Preference.OnPreferenceChangeListener() {
        @SuppressWarnings("unchecked cast")
        @Override
        public boolean onPreferenceChange(Preference preference, Object value) {
            String stringValue = value.toString();
            if (activityIntent == null || stringValue.isEmpty())
                return true;
            Intent update = new Intent(preference.getContext(), GrappboxJustInTimeService.class);
            update.setAction(GrappboxJustInTimeService.ACTION_ADD_USER_TO_PROJECT);
            Bundle arg = new Bundle();
            arg.putString(preference.getKey(), stringValue);
            update.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, activityIntent.getLongExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, -1));
            update.putExtra(GrappboxJustInTimeService.EXTRA_MAIL, stringValue);
            preference.getContext().startService(update);
            return true;
        }
    };

    private static Preference.OnPreferenceChangeListener sBindPreferenceSummaryToValueListenerCustomers = new Preference.OnPreferenceChangeListener(){

        @Override
        public boolean onPreferenceChange(Preference preference, Object value) {
            String stringValue = value.toString();
            if (activityIntent == null || stringValue.isEmpty())
                return true;
            Intent add = new Intent(preference.getContext(), GrappboxJustInTimeService.class);
            add.setAction(GrappboxJustInTimeService.ACTION_ADD_CUSTOMER_ACCESS);
            add.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, activityIntent.getLongExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, -1));
            add.putExtra(GrappboxJustInTimeService.EXTRA_NAME, stringValue);
            preference.getContext().startService(add);
            return true;
        }
    };

    class PagerAdapter extends FragmentStatePagerAdapter{

        public PagerAdapter(FragmentManager fm) {
            super(fm);
        }

        @Override
        public CharSequence getPageTitle(int position) {
            switch (position){
                case 0:
                    return "Settings";
                case 1:
                    return "Users";
                case 2:
                    return "Customer Access";
                case 3:
                    return "Roles";
            }
            return super.getPageTitle(position);
        }

        @Override
        public Fragment getItem(int position) {
            switch (position){
                case 0:
                    return new SettingsPageFragment();
                case 1:
                    return new UserPageFragment();
                case 2:
                    return new CustomerPageFragment();
                case 3:
                    return new RolePageFragment();
            }
            return null;
        }

        @Override
        public int getCount() {
            return 4;
        }
    }

    private static void bindPreferenceSummaryToValue(Preference preference, int page) {
        // Set the listener to watch for value changes.
        switch (page){
            case 0:
                preference.setOnPreferenceChangeListener(sBindPreferenceSummaryToValueListenerGeneral);
                break;
            case 1:
                preference.setOnPreferenceChangeListener(sBindPreferenceSummaryToValueListenerUsers);
                break;
            case 2:
                preference.setOnPreferenceChangeListener(sBindPreferenceSummaryToValueListenerCustomers);
                break;
            case 3:
                break;
        }
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_project_settings);
        setSupportActionBar((android.support.v7.widget.Toolbar) findViewById(R.id.toolbar));
        getSupportActionBar().setHomeButtonEnabled(true);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        ViewPager viewPager = ((ViewPager)findViewById(R.id.viewPager));
        viewPager.setOffscreenPageLimit(4);
        viewPager.setAdapter(new PagerAdapter(getSupportFragmentManager()));
    }

    public static class SettingsPageFragment extends Fragment{
        @Nullable
        @Override
        public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
            return inflater.inflate(R.layout.wrapper_settings_preference, container, false);
        }
    }

    public static class UserPageFragment extends Fragment{
        @Nullable
        @Override
        public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
            return inflater.inflate(R.layout.wrapper_users_preference, container, false);
        }
    }

    public static class CustomerPageFragment extends Fragment{
        @Nullable
        @Override
        public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
            return inflater.inflate(R.layout.wrapper_customer_preference, container, false);
        }
    }

    public static class RolePageFragment extends Fragment{
        @Nullable
        @Override
        public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
            return inflater.inflate(R.layout.wrapper_roles_preference, container, false);
        }
    }

    @TargetApi(Build.VERSION_CODES.HONEYCOMB)
    public static class GeneralPreferenceFragment extends PreferenceFragment implements LoaderManager.LoaderCallbacks<Cursor> {
        private static Preference.OnPreferenceClickListener onClickRecoverProject = new Preference.OnPreferenceClickListener() {
            @Override
            public boolean onPreferenceClick(Preference preference) {
                if (activityIntent == null)
                    return false;
                Intent recover = new Intent(preference.getContext(), GrappboxJustInTimeService.class);
                recover.setAction(GrappboxJustInTimeService.ACTION_RETRIEVE_PROJECT);
                recover.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, activityIntent.getLongExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, -1));
                preference.getContext().startService(recover);
                return true;
            }
        };

        private static Preference.OnPreferenceClickListener onClickDeleteProject = new Preference.OnPreferenceClickListener() {
            @Override
            public boolean onPreferenceClick(Preference preference) {
                if (activityIntent == null)
                    return false;
                Intent delete = new Intent(preference.getContext(), GrappboxJustInTimeService.class);
                delete.setAction(GrappboxJustInTimeService.ACTION_REMOVE_PROJECT);
                delete.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, activityIntent.getLongExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, -1));
                preference.getContext().startService(delete);
                return true;
            }
        };

        public void onActivityCreated(Bundle savedInstanceState) {
            super.onActivityCreated(savedInstanceState);
            ((AppCompatActivity)getActivity()).getSupportLoaderManager().initLoader(0, null, this);
        }

        @Override
        public void onCreate(Bundle savedInstanceState) {
            super.onCreate(savedInstanceState);
            addPreferencesFromResource(R.xml.pref_settings_project);

            bindPreferenceSummaryToValue(findPreference("name"), 0);
            bindPreferenceSummaryToValue(findPreference("description"), 0);
            bindPreferenceSummaryToValue(findPreference("company"), 0);
            bindPreferenceSummaryToValue(findPreference("email"), 0);
            bindPreferenceSummaryToValue(findPreference("phone"), 0);
            bindPreferenceSummaryToValue(findPreference("facebook"), 0);
            bindPreferenceSummaryToValue(findPreference("twitter"), 0);
        }

        @Override
        public Loader<Cursor> onCreateLoader(int id, Bundle args) {
            if (id == LOADER_GENERAL_SETTINGS)
                return new CursorLoader(getActivity(), GrappboxContract.ProjectEntry.CONTENT_URI, null, GrappboxContract.ProjectEntry._ID+"=?", new String[]{String.valueOf(getActivity().getIntent().getLongExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, -1))}, null);
            return null;
        }

        @Override
        public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
            final String[] preferences = {"name", "description", "company", "email", "phone", "twitter", "facebook"};
            if (!data.moveToFirst() || loader.getId() != 0)
                return;
            boolean deleted = !data.isNull(data.getColumnIndex(GrappboxContract.ProjectEntry.COLUMN_DATE_DELETED_UTC));
            for(String pref : preferences){
                Preference view = findPreference(pref);
                String value = data.getString(data.getColumnIndex(Utils.Database.sProjectApiDBMap.get(pref)));
                view.setSummary(value);
                view.setDefaultValue(value);
                if (deleted)
                    view.setEnabled(false);
            }
            Preference view = findPreference("delete");
            if (deleted){
                view.setTitle("Recover the project");
                view.setOnPreferenceClickListener(onClickRecoverProject);
            } else {
                view.setTitle("Delete the project");
                view.setOnPreferenceClickListener(onClickDeleteProject);
            }
        }

        @Override
        public void onLoaderReset(Loader<Cursor> loader) {

        }
    }

    @TargetApi(Build.VERSION_CODES.HONEYCOMB)
    public static class UserPreferenceFragment extends PreferenceFragment implements LoaderManager.LoaderCallbacks<Cursor> {

        @Override
        public void onActivityCreated(Bundle savedInstanceState) {
            super.onActivityCreated(savedInstanceState);
            ((AppCompatActivity)getActivity()).getSupportLoaderManager().initLoader(1, null, this);
        }

        @Override
        public void onCreate(Bundle savedInstanceState) {
            super.onCreate(savedInstanceState);
            addPreferencesFromResource(R.xml.pref_user_project);

            bindPreferenceSummaryToValue(findPreference("add_user"), 1);
        }

        @Override
        public Loader<Cursor> onCreateLoader(int id, Bundle args) {
            String localProjectID = String.valueOf(getActivity().getIntent().getLongExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, -1));
            if (id == LOADER_USER_LIST){
                final String[] projection = {
                        " DISTINCT "+GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry._ID,
                        GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_FIRSTNAME,
                        GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_LASTNAME,
                        GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_DATE_BIRTHDAY_UTC,
                        GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_URI_AVATAR,
                        GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_CONTACT_EMAIL,
                };
                return new CursorLoader(getActivity(), GrappboxContract.UserEntry.buildUserWithProject(), projection, GrappboxContract.ProjectEntry.TABLE_NAME+"."+GrappboxContract.ProjectEntry._ID+"=?", new String[]{localProjectID}, null);
            }
            else if (id == LOADER_ROLES_LIST){
                final String[] projection = {
                        " DISTINCT "+GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry._ID,
                        GrappboxContract.RolesEntry.TABLE_NAME + "." + GrappboxContract.RolesEntry.COLUMN_NAME
                };
                return new CursorLoader(getActivity(), GrappboxContract.RolesEntry.CONTENT_URI, projection, GrappboxContract.ProjectEntry.TABLE_NAME + "." + GrappboxContract.ProjectEntry._ID+"=?", new String[]{localProjectID}, null);
            }
            return null;
        }

        @Override
        public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
            if (data == null || !data.moveToFirst())
                return;
            int loaderId = loader.getId();
            if (loaderId == LOADER_USER_LIST) {
                PreferenceCategory screen = (PreferenceCategory) findPreference("user_container");
                screen.removeAll();
                do {
                    Preference user = new UserPreference(getActivity(), new UserModel(data), getActivity().getIntent().getLongExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, -1));
                    user.setKey("user_" + String.valueOf(data.getLong(0)));
                    screen.addPreference(user);
                } while (data.moveToNext());
            }
        }

        @Override
        public void onLoaderReset(Loader<Cursor> loader) {

        }
    }

    @TargetApi(Build.VERSION_CODES.HONEYCOMB)
    public static class CustomerPreferenceFragment extends PreferenceFragment implements LoaderManager.LoaderCallbacks<Cursor> {
        @Override
        public void onCreate(Bundle savedInstanceState) {
            super.onCreate(savedInstanceState);
            addPreferencesFromResource(R.xml.pref_customer_project);
            bindPreferenceSummaryToValue(findPreference("add_customer_access"), 2);
            //bindPreferenceSummaryToValue(findPreference("example_text"));
            //bindPreferenceSummaryToValue(findPreference("example_list"));
        }

        @Override
        public void onActivityCreated(Bundle savedInstanceState) {
            super.onActivityCreated(savedInstanceState);
            ((AppCompatActivity)getActivity()).getSupportLoaderManager().initLoader(LOADER_CUSTOMER_ACCESS, null, this);
        }

        @Override
        public Loader<Cursor> onCreateLoader(int id, Bundle args) {
            if (id == LOADER_CUSTOMER_ACCESS){
                return new CursorLoader(getActivity(), CustomerAccessEntry.buildCustomerAccessWithProject, CustomerAccessModel.projection, GrappboxContract.ProjectEntry.TABLE_NAME + "." + GrappboxContract.ProjectEntry._ID + "=?", new String[]{String.valueOf(getActivity().getIntent().getLongExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, -1))}, null);
            }
            return null;
        }

        @Override
        public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
            if (data == null || !data.moveToFirst())
                return;
            if (loader.getId() == LOADER_CUSTOMER_ACCESS){
                PreferenceCategory category = (PreferenceCategory) findPreference("customer_access_container");
                category.removeAll();
                do{
                    category.addPreference(new CustomerAccessPreference(getActivity(), new CustomerAccessModel(data)));
                }while(data.moveToNext());
            }
        }

        @Override
        public void onLoaderReset(Loader<Cursor> loader) {

        }
    }

    @TargetApi(Build.VERSION_CODES.HONEYCOMB)
    public static class RolePreferenceFragment extends PreferenceFragment implements LoaderManager.LoaderCallbacks<Cursor> {
        @Override
        public void onCreate(Bundle savedInstanceState) {
            super.onCreate(savedInstanceState);
            addPreferencesFromResource(R.xml.pref_roles_project);


            //bindPreferenceSummaryToValue(findPreference("example_text"));
            //bindPreferenceSummaryToValue(findPreference("example_list"));
        }

        @Override
        public Loader<Cursor> onCreateLoader(int id, Bundle args) {
            if (id == LOADER_ROLES_LIST){
                //TODO : cursor loader return new CursorLoader(getActivity(), , )
            }
            return null;
        }

        @Override
        public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
            if (loader.getId() == LOADER_ROLES_LIST){

            }
        }

        @Override
        public void onLoaderReset(Loader<Cursor> loader) {

        }
    }
}
