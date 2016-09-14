package com.grappbox.grappbox;

import android.accounts.Account;
import android.app.LoaderManager;
import android.content.CursorLoader;
import android.content.Intent;
import android.content.Loader;
import android.content.res.Resources;
import android.database.Cursor;
import android.graphics.Color;
import android.graphics.drawable.ColorDrawable;
import android.os.Build;
import android.os.Bundle;
import android.provider.Settings;
import android.support.design.widget.AppBarLayout;
import android.support.design.widget.NavigationView;
import android.support.v4.app.FragmentManager;
import android.support.v4.content.res.ResourcesCompat;
import android.support.v4.graphics.drawable.DrawableCompat;
import android.support.v4.view.GravityCompat;
import android.support.v4.view.WindowCompat;
import android.support.v4.widget.DrawerLayout;
import android.support.v7.app.ActionBarDrawerToggle;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.app.WindowDecorActionBar;
import android.support.v7.widget.Toolbar;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.view.WindowManager;
import android.widget.TextView;

import com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;
import com.grappbox.grappbox.project_fragments.BugtrackerFragment;
import com.grappbox.grappbox.project_fragments.CalendarFragment;
import com.grappbox.grappbox.project_fragments.CloudFragment;
import com.grappbox.grappbox.project_fragments.DashboardFragment;
import com.grappbox.grappbox.project_fragments.GanttFragment;
import com.grappbox.grappbox.project_fragments.TaskFragment;
import com.grappbox.grappbox.project_fragments.TimelineFragment;
import com.grappbox.grappbox.project_fragments.WhiteboardFragment;
import com.grappbox.grappbox.singleton.Session;

public class ProjectActivity extends AppCompatActivity implements LoaderManager.LoaderCallbacks<Cursor>, NavigationView.OnNavigationItemSelectedListener, FragmentManager.OnBackStackChangedListener {
    private static final String LOG_TAG = ProjectActivity.class.getSimpleName();
    public static final String EXTRA_PROJECT_ID = "com.grappbox.grappbox.ProjectActivity.EXTRA_PROJECT_ID";
    public static final String EXTRA_PROJECT_NAME = "com.grappbox.grappbox.ProjectActivity.EXTRA_PROJECT_NAME";

    public static final String FRAGMENT_TAG_DASHBOARD = "FTAG_DASHBOARD";
    public static final String FRAGMENT_TAG_CALENDAR = "FTAG_CALENDAR";
    public static final String FRAGMENT_TAG_CLOUD = "FTAG_CLOUD";
    public static final String FRAGMENT_TAG_TIMELINE = "FTAG_TIMELINE";
    public static final String FRAGMENT_TAG_BUGTRACKER = "FTAG_BUGTRACKER";
    public static final String FRAGMENT_TAG_TASK = "FTAG_TASK";
    public static final String FRAGMENT_TAG_GANTT = "FTAG_GANTT";
    public static final String FRAGMENT_TAG_WHITEBOARD = "FTAG_WHITEBOARD";

    private static final int COLUMN_PROJECT_ID = 0;

    private static final int COLUMN_USER_ID = 0;
    private static final int COLUMN_USER_FIRSTNAME = 1;
    private static final int COLUMN_USER_LASTNAME = 2;
    private static final int COLUMN_USER_AVATAR_URI = 3;

    public static final int LOADER_PROJECT_INFOS = 0;
    public static final int LOADER_ADDED_USER_INFOS = 1;

    private Toolbar mToolbar;
    private NavigationView mNavView;
    private int mCurrentNavSelected = -1;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_project);
        mToolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(mToolbar);
        if (getSupportActionBar() != null){
            getSupportActionBar().setElevation(0.f);
        }
        mNavView = (NavigationView) findViewById(R.id.nav_view);
        mNavView.setNavigationItemSelectedListener(this);
        if (savedInstanceState == null){
            getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, new DashboardFragment(), FRAGMENT_TAG_DASHBOARD).commit();
            mCurrentNavSelected = R.id.nav_dashboard;
            setTheme(R.style.DashboardTheme);
            mNavView.getMenu().getItem(0).setChecked(true);
            mNavView.setItemTextColor(ResourcesCompat.getColorStateList(getResources(), R.color.main_menu_colors, getTheme()));
            mNavView.setItemIconTintList(ResourcesCompat.getColorStateList(getResources(), R.color.main_menu_colors, getTheme()));
        }
        getSupportFragmentManager().addOnBackStackChangedListener(this);
        if (getIntent() == null){
            Intent chooseProject = new Intent(this, ChooseProjectActivity.class);
            startActivity(chooseProject);
            finish();
            return;
        }
        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        ActionBarDrawerToggle toggle = new ActionBarDrawerToggle(
                this, drawer, mToolbar, R.string.navigation_drawer_open, R.string.navigation_drawer_close);
        drawer.setDrawerListener(toggle);
        toggle.syncState();

        Bundle projectId = new Bundle();
        projectId.putLong(EXTRA_PROJECT_ID, getIntent().getLongExtra(EXTRA_PROJECT_ID, -1));
        getLoaderManager().initLoader(LOADER_PROJECT_INFOS, projectId, this);
        getLoaderManager().initLoader(LOADER_ADDED_USER_INFOS, null, this);
    }

    private void syncNavDrawer(String currentTag){
        switch (currentTag){
            case FRAGMENT_TAG_DASHBOARD:
                mCurrentNavSelected = R.id.nav_dashboard;
                break;
            case FRAGMENT_TAG_CALENDAR:
                mCurrentNavSelected = R.id.nav_calendar;
                break;
            case FRAGMENT_TAG_CLOUD:
                mCurrentNavSelected = R.id.nav_cloud;
                break;
            case FRAGMENT_TAG_TIMELINE:
                mCurrentNavSelected = R.id.nav_timeline;
                break;
            case FRAGMENT_TAG_TASK:
                mCurrentNavSelected = R.id.nav_tasks;
                break;
            case FRAGMENT_TAG_GANTT:
                mCurrentNavSelected = R.id.nav_gantt;
                break;
            case FRAGMENT_TAG_WHITEBOARD:
                mCurrentNavSelected = R.id.nav_whiteboard;
                break;
        }
        mNavView.setCheckedItem(mCurrentNavSelected);
    }

    @Override
    public void onBackPressed() {
        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        if (drawer.isDrawerOpen(GravityCompat.START)) {
            drawer.closeDrawer(GravityCompat.START);
        } else {
            super.onBackPressed();
        }
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.dashboard, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

        //noinspection SimplifiableIfStatement
        if (id == R.id.action_settings) {
            return true;
        }

        return super.onOptionsItemSelected(item);
    }

    @SuppressWarnings("StatementWithEmptyBody")
    @Override
    public boolean onNavigationItemSelected(MenuItem item) {
        // Handle navigation view item clicks here.
        int id = item.getItemId();
        if (id == mCurrentNavSelected)
            return false;
        switch (id){
            case R.id.nav_dashboard:
                getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, new DashboardFragment(), FRAGMENT_TAG_DASHBOARD).addToBackStack(null).commit();
                break;
            case R.id.nav_calendar:
                getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, new CalendarFragment(), FRAGMENT_TAG_CALENDAR).addToBackStack(null).commit();
                break;
            case R.id.nav_cloud:
                getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, new CloudFragment(), FRAGMENT_TAG_CLOUD).addToBackStack(null).commit();
                break;
            case R.id.nav_timeline:
                getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, new TimelineFragment(), FRAGMENT_TAG_TIMELINE).addToBackStack(null).commit();
                break;
            case R.id.nav_bugtracker:
                getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, new BugtrackerFragment(), FRAGMENT_TAG_BUGTRACKER).addToBackStack(null).commit();
                break;
            case R.id.nav_tasks:
                getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, new TaskFragment(), FRAGMENT_TAG_TASK).addToBackStack(null).commit();
                break;
            case R.id.nav_gantt:
                getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, new GanttFragment(), FRAGMENT_TAG_GANTT).addToBackStack(null).commit();
                break;
            case R.id.nav_whiteboard:
                getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, new WhiteboardFragment(), FRAGMENT_TAG_WHITEBOARD).addToBackStack(null).commit();
                break;
            case R.id.nav_project_settings:
                break;
            case R.id.nav_change_project:
                break;
            case R.id.nav_change_account:
                break;
            default:
                throw new UnsupportedOperationException(getString(R.string.error_hacker_detected));
        }
        mCurrentNavSelected = id;
        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        drawer.closeDrawer(GravityCompat.START);
        return true;
    }



    @Override
    public Loader<Cursor> onCreateLoader(int i, Bundle bundle) {
        if (i == LOADER_PROJECT_INFOS){
            if (bundle.getLong(EXTRA_PROJECT_ID) == -1)
                return null;
            return new CursorLoader(this, ProjectEntry.buildProjectWithLocalIdUri(bundle.getLong(EXTRA_PROJECT_ID)), new String[]{ProjectEntry.COLUMN_NAME}, null, null, null);
        } else if (i == LOADER_ADDED_USER_INFOS) {
            Account currentAccount = Session.getInstance(this).getCurrentAccount();
            return new CursorLoader(this, UserEntry.CONTENT_URI, new String[]{UserEntry._ID, UserEntry.COLUMN_FIRSTNAME, UserEntry.COLUMN_LASTNAME, UserEntry.COLUMN_URI_AVATAR},
                    UserEntry.COLUMN_CONTACT_EMAIL + "=?", new String[]{currentAccount.name}, null);
        }
        return null;
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor cursor) {
        if (!cursor.moveToFirst())
            return;
        if (loader.getId() == LOADER_PROJECT_INFOS){
            setTitle(cursor.getString(COLUMN_PROJECT_ID));
        } else if (loader.getId() == LOADER_ADDED_USER_INFOS){
            TextView mail = (TextView) mNavView.getHeaderView(0).findViewById(R.id.connected_user_email);
            TextView name = (TextView) mNavView.getHeaderView(0).findViewById(R.id.connected_user_name);
            String username = cursor.getString(COLUMN_USER_FIRSTNAME) + " " + cursor.getString(COLUMN_USER_LASTNAME);

            mail.setText(Session.getInstance(this).getCurrentAccount().name);
            name.setText(username);
        }
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {
    }

    @Override
    public void onBackStackChanged() {
        String tag = getSupportFragmentManager().findFragmentById(R.id.fragment_container).getTag();
        syncNavDrawer(tag);
        int newTheme = -1;
        switch (tag){
            case FRAGMENT_TAG_DASHBOARD:
                newTheme = R.style.DashboardTheme;
                break;
            case FRAGMENT_TAG_CALENDAR:
                newTheme = R.style.CalendarTheme;
                break;
            case FRAGMENT_TAG_CLOUD:
                newTheme = R.style.CloudTheme;
                break;
            case FRAGMENT_TAG_TIMELINE:
                newTheme = R.style.TimelineTheme;
                break;
            case FRAGMENT_TAG_BUGTRACKER:
                newTheme = R.style.BugtrackerTheme;
                break;
            case FRAGMENT_TAG_TASK:
                newTheme = R.style.TaskTheme;
                break;
            case FRAGMENT_TAG_GANTT:
                newTheme = R.style.GanttTheme;
                break;
            case FRAGMENT_TAG_WHITEBOARD:
                newTheme = R.style.WhiteboardTheme;
                break;
            default:
                break;
        }
        setTheme(newTheme);
        getApplication().setTheme(newTheme);

        mNavView.setItemTextColor(ResourcesCompat.getColorStateList(getResources(), R.color.main_menu_colors, getTheme()));
        mNavView.setItemIconTintList(ResourcesCompat.getColorStateList(getResources(), R.color.main_menu_colors, getTheme()));
        mToolbar.setBackgroundColor(Utils.Color.getThemeAccentColor(this));

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            getWindow().setStatusBarColor(Utils.Color.getThemeAccentColor(this));
        }
    }
}
