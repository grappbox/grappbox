package com.grappbox.grappbox.grappbox;

import android.app.Activity;
import android.content.Intent;
import android.content.pm.ActivityInfo;
import android.content.res.Configuration;
import android.graphics.Bitmap;
import android.graphics.drawable.Drawable;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Bundle;
import android.provider.MediaStore;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentTransaction;
import android.support.v4.content.res.ResourcesCompat;
import android.util.Log;
import android.view.View;
import android.support.design.widget.NavigationView;
import android.support.v4.view.GravityCompat;
import android.support.v4.widget.DrawerLayout;
import android.support.v7.app.ActionBarDrawerToggle;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.ImageButton;
import android.widget.TextView;

import com.google.common.collect.ImmutableMap;
import com.grappbox.grappbox.grappbox.BugTracker.BugTrackerFragment;
import com.grappbox.grappbox.grappbox.Calendar.AgendaFragment;
import com.grappbox.grappbox.grappbox.Cloud.CloudExplorerFragment;
import com.grappbox.grappbox.grappbox.Dashboard.DashboardFragment;
import com.grappbox.grappbox.grappbox.Dashboard.DashboardProjectListFragment;
import com.grappbox.grappbox.grappbox.Gantt.GanttFragment;
import com.grappbox.grappbox.grappbox.Gantt.TaskFragment;
import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.AccessModel;
import com.grappbox.grappbox.grappbox.Model.GetAccessesTask;
import com.grappbox.grappbox.grappbox.Model.ProjectModel;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.Model.UserProjectTask;
import com.grappbox.grappbox.grappbox.Project.CreateProjectActivity;
import com.grappbox.grappbox.grappbox.Project.CreateProjectPreferenceActivity;
import com.grappbox.grappbox.grappbox.Settings.UserProfileActivity;
import com.grappbox.grappbox.grappbox.Timeline.TimelineFragment;
import com.grappbox.grappbox.grappbox.Whiteboard.WhiteboardListFragment;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;
import java.util.Objects;

public class MainActivity extends AppCompatActivity
        implements NavigationView.OnNavigationItemSelectedListener, SessionAdapter.SessionListener, FragmentManager.OnBackStackChangedListener{

    public static final int PICK_DOCUMENT_FROM_SYSTEM = 1;
    public static final int PICK_DOCUMENT_SECURED_FROM_SYSTEM = 2;
    public static final int REFRESH_AFTER_PROJECT_CREATION = 3;
    static final Map<Integer, String> MENU_MAPING_AUTH = ImmutableMap.<Integer, String>builder()
            .put(R.id.nav_whiteboard, "whiteboard")
            .put(R.id.nav_Bugtracker, "bugtracker")
            .put(R.id.nav_Cloud, "cloud")
            .put(R.id.nav_Gantt, "gantt")
            .put(R.id.nav_tasks, "task").build();

    private static final String TAG = MainActivity.class.getSimpleName();
    private final MainActivity _currentActivity = this;
    private Toolbar _toolbar;
    private FragmentManager _fragmentManager;
    private ActionBarDrawerToggle _actionBarDrawerToggle;
    private DrawerLayout _Drawer;
    private ArrayList<ProjectModel> _projectMenuList;
    private boolean _bMenuClosed;
    private Map<String, Runnable> _toolbarTitleHandler;

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (requestCode == REFRESH_AFTER_PROJECT_CREATION && resultCode == Activity.RESULT_OK){
            refreshCurrentFragment();
        }
    }

    private void refreshCurrentFragment()
    {
        Fragment frg = null;
        frg = getSupportFragmentManager().findFragmentByTag("Your_Fragment_TAG");
        final FragmentTransaction ft = getSupportFragmentManager().beginTransaction();
        ft.detach(frg);
        ft.attach(frg);
        ft.commit();

    }

    private void OnHeaderClicked(View header)
    {
        ImageButton btn = (ImageButton) header.findViewById(R.id.imgbtn_current_project);
        Drawable arrow_up = ResourcesCompat.getDrawable(getResources(), R.drawable.ic_arrow_up, getTheme());
        Drawable arrow_down = ResourcesCompat.getDrawable(getResources(), R.drawable.ic_arrow_down, getTheme());
        NavigationView navView = (NavigationView) findViewById(R.id.nav_view);
        TextView txt_projectSelected = (TextView) findViewById(R.id.txt_current_project);

        assert arrow_down != null && arrow_up != null;
        if (_bMenuClosed)
        {
            if (navView == null)
                return;
            Menu menu = navView.getMenu();

            menu.clear();
            for (ProjectModel model : _projectMenuList) {
                if (!model.isValid())
                    continue;
                MenuItem item = menu.add(model.getName());
                item.setOnMenuItemClickListener(new MenuItem.OnMenuItemClickListener() {
                    @Override
                    public boolean onMenuItemClick(MenuItem item) {
                        _bMenuClosed = !_bMenuClosed;
                        if (txt_projectSelected != null && item != null)
                            txt_projectSelected.setText(item.getTitle());
                        SessionAdapter session = SessionAdapter.getInstance();
                        session.setCurrentSelectedProject(model.getId());
                        session.setCurrentSelectedProjectName(model.getName());
                        navView.getMenu().clear();
                        navView.inflateMenu(R.menu.activity_main_drawer);
                        btn.setImageDrawable(arrow_down);
                        return false;
                    }
                });
            }

            MenuItem item_settings = menu.add(R.string.str_project_settings).setIcon(ResourcesCompat.getDrawable(getResources(), R.drawable.ic_settings, getTheme()));
            item_settings.setOnMenuItemClickListener(new MenuItem.OnMenuItemClickListener() {
                @Override
                public boolean onMenuItemClick(MenuItem item) {
                    _bMenuClosed = !_bMenuClosed;
                    SessionAdapter session = SessionAdapter.getInstance();
                    Intent intent = new Intent(getBaseContext(), ProjectSettingsActivity.class);

                    intent.putExtra(ProjectSettingsActivity.EXTRA_PROJECT_ID, (session.getCurrentSelectedProject()));
                    intent.putExtra(ProjectSettingsActivity.EXTRA_NO_HEADERS, true);
                    intent.putExtra(ProjectSettingsActivity.EXTRA_PROJECT_NAME, session.getCurrentSelectedProjectName());
                    for (int i = 0; i < _projectMenuList.size(); ++i)
                    {
                        ProjectModel model = _projectMenuList.get(i);
                        if (Objects.equals(model.getId(), session.getCurrentSelectedProject()))
                        {
                            intent.putExtra(ProjectSettingsActivity.EXTRA_PROJECT_MODEL, model);
                            break;
                        }
                    }
                    intent.putExtra(ProjectSettingsActivity.EXTRA_SHOW_FRAGMENT, "com.grappbox.grappbox.grappbox.ProjectSettingsActivity$GeneralPreferenceFragment");
                    startActivity(intent);
                    return false;
                }
            });
            item_settings.setEnabled(SessionAdapter.getInstance().getCurrentSelectedProject() != null && !SessionAdapter.getInstance().getCurrentSelectedProject().isEmpty());
            MenuItem create_project = menu.add(R.string.str_project_create).setIcon(ResourcesCompat.getDrawable(getResources(), R.drawable.ic_settings, getTheme()));
            create_project.setOnMenuItemClickListener(new MenuItem.OnMenuItemClickListener(){
                public boolean onMenuItemClick(MenuItem item)
                {
                    Intent intent = new Intent(getBaseContext(), CreateProjectPreferenceActivity.class);
                    startActivityForResult(intent, REFRESH_AFTER_PROJECT_CREATION);
//                    startActivity(intent);

                    return false;
                }
            });
            btn.setImageDrawable(arrow_up);

        }
        else
        {
            onSelectedProjectChange(SessionAdapter.getInstance().getCurrentSelectedProject());
            btn.setImageDrawable(arrow_down);
        }
        _bMenuClosed = !_bMenuClosed;
    }

    protected void toolbarHandlerInitialize()
    {
        _toolbarTitleHandler = new HashMap<>();

        _toolbarTitleHandler.put(DashboardFragment.class.getName(), () -> changeToolbarTitle("Dashboard"));
        _toolbarTitleHandler.put(DashboardProjectListFragment.class.getName(), () -> changeToolbarTitle("Dashboard"));
        _toolbarTitleHandler.put(WhiteboardListFragment.class.getName(), () -> changeToolbarTitle("Whiteboard"));
        _toolbarTitleHandler.put(AgendaFragment.class.getName(), () -> changeToolbarTitle("Calendar"));
        _toolbarTitleHandler.put(TimelineFragment.class.getName(), () -> changeToolbarTitle("Timeline"));
        _toolbarTitleHandler.put(CloudExplorerFragment.class.getName(), () -> changeToolbarTitle("Cloud"));
        _toolbarTitleHandler.put(BugTrackerFragment.class.getName(), () -> changeToolbarTitle("Bug Tracker - Bug list"));
        _toolbarTitleHandler.put(GanttFragment.class.getName(), () -> changeToolbarTitle("GANTT"));
        _toolbarTitleHandler.put(TaskFragment.class.getName(), () -> changeToolbarTitle("Tasks - List"));
    }

    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        toolbarHandlerInitialize();
        _bMenuClosed = true;
        _toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(_toolbar);

        _projectMenuList = new ArrayList<>();

        _Drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        _actionBarDrawerToggle = new ActionBarDrawerToggle(
                this, _Drawer, _toolbar, R.string.navigation_drawer_open, R.string.navigation_drawer_close);
        _actionBarDrawerToggle.setToolbarNavigationClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                UserProjectTask task = new UserProjectTask(_currentActivity);
                task.execute();
            }
        });
        UserProjectTask task = new UserProjectTask(this);
        task.execute();
        _Drawer.setDrawerListener(_actionBarDrawerToggle);
        _actionBarDrawerToggle.syncState();
        _actionBarDrawerToggle.setDrawerIndicatorEnabled(true);
        NavigationView navigationView = (NavigationView) findViewById(R.id.nav_view);
        navigationView.setNavigationItemSelectedListener(this);

        View headerView = navigationView.getHeaderView(0);
        TextView text = (TextView)headerView.findViewById(R.id.nav_head_name_user);
        TextView txt_selectedProject = (TextView) headerView.findViewById(R.id.txt_current_project);
        String name = SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN) + " " + SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN);

        headerView.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) { OnHeaderClicked(v); }
        });

        if (this.getIntent() != null)
        {
            Intent loader = this.getIntent();
            String cloudPath = loader.getStringExtra(CloudExplorerFragment.CLOUDEXPLORER_PATH);
            if (cloudPath != null)
            {
                CloudExplorerFragment fragment = new CloudExplorerFragment();
                fragment.setPath(cloudPath);
                _fragmentManager = getSupportFragmentManager();
                _fragmentManager.beginTransaction().replace(R.id.content_frame, fragment).commit();
                return;
            }
        }
        if (savedInstanceState == null) {
            _fragmentManager = getSupportFragmentManager();
            _fragmentManager.addOnBackStackChangedListener(this);
            _fragmentManager.beginTransaction().replace(R.id.content_frame, new DashboardProjectListFragment(), DashboardFragment.TAG).commit();
        }
        SessionAdapter.getInstance().addEventSeeker(this);
        onSelectedProjectChange(SessionAdapter.getInstance().getCurrentSelectedProject());
        if (!SessionAdapter.getInstance().getCurrentSelectedProjectName().isEmpty())
            txt_selectedProject.setText(SessionAdapter.getInstance().getCurrentSelectedProjectName());
    }

    //SessionAdapter.SessionListener interface implementation
    @Override
    public void onSelectedProjectChange(String projectID) {
        NavigationView nv = (NavigationView) findViewById(R.id.nav_view);
        if (nv == null || projectID == null)
            return;
        nv.getMenu().clear();
        nv.inflateMenu(projectID.isEmpty() ? R.menu.activity_main_drawer_no_project : R.menu.activity_main_drawer);
        GetAccessesTask task = new GetAccessesTask(new GetAccessesTask.TaskListener() {
            @Override
            public void onTaskFetched(boolean success, JSONObject data) {
                if (!success || data == null)
                    return;
                try {
                    SynchroniseMenu(parseAuthorizations(data, projectID));
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
        }, this);
        task.execute();
        ProjectSettingsActivity.GetProjectInfosTask getInfos = new ProjectSettingsActivity.GetProjectInfosTask(this, new ProjectSettingsActivity.GetProjectInfosTask.ProjectInfosListener() {
            @Override
            public void onDataFetched(JSONObject json) {
                if (json == null)
                    return;
                try {
                    SessionAdapter.getInstance().setCurrentSelectedProjectInfos(new ProjectModel(json));
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
        });
        getInfos.execute();
    }
    //SessionAdapter.SessionListener END
    private AccessModel parseAuthorizations(JSONObject data, String projectID) throws JSONException {
        AccessModel authorizations = new AccessModel();
        JSONArray array = data.getJSONArray("array");
        for (int i = 0; i < array.length(); ++i)
        {
            JSONObject current = array.getJSONObject(i);
            JSONObject curProject = current.getJSONObject("project");
            if (curProject.getString("id").equals(projectID))
            {
                JSONObject curRoles = current.getJSONObject("role");
                JSONObject values = curRoles.getJSONObject("values");
                Iterator<String> it = values.keys();
                do {
                    String key = it.next();
                    int value = values.getInt(key);
                    authorizations.setAuthorization(key, AccessModel.AccessRights.valueOf(value));
                } while(it.hasNext());
            }
        }
        return authorizations;
    }

    private void SynchroniseMenu(AccessModel auths)
    {
        NavigationView nv = (NavigationView) findViewById(R.id.nav_view);
        SessionAdapter.getInstance().setAuthorizations(auths);
        if (nv == null)
            return;
        Menu menu = nv.getMenu();
        if (menu == null)
            return;
        for (int i = 0; i < menu.size(); ++i)
        {
            MenuItem item = menu.getItem(i);

            if (item == null)
                continue;
            if (item.getItemId() == R.id.nav_Timeline)
            {
                AccessModel.AccessRights teamTimeline, customerTimeline;
                teamTimeline = auths.getAuthorization("teamTimeline");
                customerTimeline = auths.getAuthorization("customerTimeline");
                if (customerTimeline == null || teamTimeline == null)
                {
                    continue;
                }

                boolean enabled = teamTimeline.AuthorizationLevel() > 0 || customerTimeline.AuthorizationLevel() > 0;
                item.setEnabled(enabled);
            }
            else if (MENU_MAPING_AUTH.containsKey(item.getItemId()) && MENU_MAPING_AUTH.get(item.getItemId()) != null)
            {
                AccessModel.AccessRights curAuth =  auths.getAuthorization(MENU_MAPING_AUTH.get(item.getItemId()));
                if (curAuth == null)
                {
                    continue;
                }

                item.setEnabled(curAuth.AuthorizationLevel() > AccessModel.AccessRights.NONE.ordinal());

            }

        }
    }

    @Override
    protected void onPostCreate(Bundle savedInstanceState) {
        super.onPostCreate(savedInstanceState);
        _actionBarDrawerToggle.syncState();
    }

    @Override
    public void onConfigurationChanged(Configuration newConfig) {
        super.onConfigurationChanged(newConfig);
        _actionBarDrawerToggle.onConfigurationChanged(newConfig);
        onSelectedProjectChange(SessionAdapter.getInstance().getCurrentSelectedProject());
    }

    @Override
    public void onBackPressed() {

        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        int nbrFrag = getSupportFragmentManager().getBackStackEntryCount();
        Fragment currentFrag = getSupportFragmentManager().findFragmentById(R.id.content_frame);
        Log.v("Count Fragment : ", String.valueOf(nbrFrag));
        Log.v("MainActivity", String.valueOf(currentFrag == null));
        if (drawer.isDrawerOpen(GravityCompat.START)) {
            drawer.closeDrawer(GravityCompat.START);
        }
        else if (currentFrag != null && currentFrag instanceof CloudExplorerFragment)
        {
            if (!((CloudExplorerFragment) currentFrag).onBackPressed())
                getSupportFragmentManager().popBackStack();
        }
        else if (nbrFrag > 0) {
            getSupportFragmentManager().popBackStack();
        } else {
            super.onBackPressed();
        }
    }

    @Override
    protected void onSaveInstanceState(Bundle outState) {
        super.onSaveInstanceState(outState);
        Log.e("MainActivity", "onSaveInstanceState : " + _toolbar.getTitle().toString());
        outState.putString("activity_title", _toolbar.getTitle().toString());
    }

    @Override
    protected void onRestoreInstanceState(Bundle savedInstanceState) {
        super.onRestoreInstanceState(savedInstanceState);
        Log.e("MainActivity", "onRestoreInstanceState : " + savedInstanceState.getString("activity_title"));
        getSupportActionBar().setTitle(savedInstanceState.getString("activity_title"));
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.main, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        int id = item.getItemId();


        if (id == R.id.action_profile) {
            startActivity(new Intent(this, UserProfileActivity.class));
            return true;
        }

        if (id == R.id.action_logout){
            APIRequestLogout api = new APIRequestLogout();
            api.execute();
            return true;
        }

        return super.onOptionsItemSelected(item);
    }

    @SuppressWarnings("StatementWithEmptyBody")
    @Override
    public boolean onNavigationItemSelected(MenuItem item) {

        int id = item.getItemId();

        Fragment fragment = null;
        setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_UNSPECIFIED);
        switch (id){

            case R.id.nav_dashboard:
                if (SessionAdapter.getInstance().getCurrentSelectedProject().equals("")) {
                    fragment = new DashboardProjectListFragment();
                } else {
                    fragment = new DashboardFragment();
                }
                break;

            case R.id.nav_whiteboard:
                fragment = new WhiteboardListFragment();
                break;

            case R.id.nav_calendar:
                fragment = new AgendaFragment();
                break;

            case R.id.nav_Timeline:
                fragment = new TimelineFragment();
                break;

            case R.id.nav_Cloud:
                fragment = new CloudExplorerFragment();
                break;

            case R.id.nav_Bugtracker:
                fragment = new BugTrackerFragment();
                break;

            case R.id.nav_Gantt:
                setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_LANDSCAPE);
                fragment = new GanttFragment();
                break;

            case R.id.nav_tasks:
                fragment = new TaskFragment();
                break;

            default:
                break;
        }
        if (_fragmentManager == null)
        {
            _fragmentManager = getSupportFragmentManager();
            _fragmentManager.addOnBackStackChangedListener(this);
        }

        if (fragment != null)
                _fragmentManager.beginTransaction().replace(R.id.content_frame, fragment).addToBackStack("").commit();
        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        drawer.closeDrawer(GravityCompat.START);
        return true;
    }

    public void setProjectList(ArrayList<ProjectModel> projectList)
    {
        _projectMenuList = projectList;
    }

    public void logoutUser()
    {
        super.onBackPressed();
        SessionAdapter.getInstance().LogoutUser();
    }

    private void changeToolbarTitle(String title)
    {
        _toolbar.setTitle(title);
    }

    @Override
    public void onBackStackChanged() {
        if (_fragmentManager == null)
            _fragmentManager = getSupportFragmentManager();
        Fragment fragment = _fragmentManager.findFragmentById(R.id.content_frame);
        if (fragment == null)
            return;
        if (_toolbarTitleHandler.containsKey(fragment.getClass().getName()))
            _toolbarTitleHandler.get(fragment.getClass().getName()).run();
        else
            changeToolbarTitle(getString(R.string.app_name));
    }

    public class APIRequestLogout extends AsyncTask<String, Void, Void> {

        @Override
        protected void onPostExecute(Void result) {
            super.onPostExecute(result);
            logoutUser();
        }

        @Override
        protected Void doInBackground(String ... param)
        {
            String resultAPI;

            try {
                APIConnectAdapter.getInstance().startConnection("accountadministration/logout/" + SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN));
                APIConnectAdapter.getInstance().setRequestConnection("GET");

                resultAPI = APIConnectAdapter.getInstance().getInputSream();

            } catch (IOException e){
                Log.e("APIConnection", "Error ", e);
                return null;
            } finally {
                APIConnectAdapter.getInstance().closeConnection();
            }
            return null;
        }

    }
}
