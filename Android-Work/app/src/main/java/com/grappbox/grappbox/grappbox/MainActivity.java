package com.grappbox.grappbox.grappbox;

import android.app.ActivityManager;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.res.ColorStateList;
import android.content.res.Configuration;
import android.graphics.drawable.BitmapDrawable;
import android.graphics.drawable.Drawable;
import android.graphics.drawable.DrawableContainer;
import android.os.AsyncTask;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.content.res.ResourcesCompat;
import android.support.v7.graphics.drawable.DrawableWrapper;
import android.support.v7.view.menu.MenuBuilder;
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
import android.widget.ArrayAdapter;
import android.widget.ImageButton;
import android.widget.LinearLayout;
import android.widget.ListAdapter;
import android.widget.Spinner;
import android.widget.SpinnerAdapter;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.BugTracker.BugTrackerFragment;
import com.grappbox.grappbox.grappbox.Calendar.AgendaFragment;
import com.grappbox.grappbox.grappbox.Cloud.CloudExplorerFragment;
import com.grappbox.grappbox.grappbox.Dashboard.DashboardFragment;
import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.ProjectMenuAdapter;
import com.grappbox.grappbox.grappbox.Model.ProjectModel;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.Model.UserProjectTask;
import com.grappbox.grappbox.grappbox.Settings.UserProfileFragment;
import com.grappbox.grappbox.grappbox.Timeline.TimelineFragment;
import com.grappbox.grappbox.grappbox.Whiteboard.WhiteboardListFragment;

import org.w3c.dom.Text;

import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class MainActivity extends AppCompatActivity
        implements NavigationView.OnNavigationItemSelectedListener{

    public static final int PICK_DOCUMENT_FROM_SYSTEM = 1;
    public static final int PICK_DOCUMENT_SECURED_FROM_SYSTEM = 2;
    private static final String TAG = MainActivity.class.getSimpleName();
    private final MainActivity _currentActivity = this;
    private Toolbar _toolbar;
    private FragmentManager _fragmentManager;
    private ActionBarDrawerToggle _actionBarDrawerToggle;
    private DrawerLayout _Drawer;
    private ArrayList<ProjectModel> _projectMenuList;

    private void OnHeaderClicked(View header)
    {
        ImageButton btn = (ImageButton) header.findViewById(R.id.imgbtn_current_project);
        Drawable arrow_up = ResourcesCompat.getDrawable(getResources(), R.drawable.ic_arrow_up, getTheme());
        Drawable arrow_down = ResourcesCompat.getDrawable(getResources(), R.drawable.ic_arrow_down, getTheme());
        NavigationView navView = (NavigationView) findViewById(R.id.nav_view);
        TextView txt_projectSelected = (TextView) findViewById(R.id.txt_current_project);

        assert arrow_down != null && arrow_up != null;
        if (btn.getDrawable().getConstantState().equals(arrow_down.getConstantState()))
        {
            Menu menu = navView.getMenu();

            menu.clear();
            for (ProjectModel model : _projectMenuList) {
                if (!model.isValid())
                    continue;
                MenuItem item = menu.add(0, model.getId(), 0, model.getName());
                item.setOnMenuItemClickListener(new MenuItem.OnMenuItemClickListener() {
                    @Override
                    public boolean onMenuItemClick(MenuItem item) {
                        txt_projectSelected.setText(item.getTitle());
                        SessionAdapter.getInstance().setCurrentSelectedProject(item.getItemId());
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
                    AlertDialog.Builder builder = new AlertDialog.Builder(_currentActivity);
                    ListAdapter adapter = new ProjectMenuAdapter(_currentActivity, R.layout.dialog_project_settings, _projectMenuList);

                    builder.setTitle(R.string.str_modif_project_settings);
                    builder.setAdapter(adapter, new DialogInterface.OnClickListener() {
                        @Override
                        public void onClick(DialogInterface dialog, int which) {
                            Intent intent = new Intent(getBaseContext(), ProjectSettingsActivity.class);

                            intent.putExtra(ProjectSettingsActivity.EXTRA_PROJECT_ID, ((ProjectModel) adapter.getItem(which)).getId());
                            intent.putExtra(ProjectSettingsActivity.EXTRA_NO_HEADERS, true);
                            intent.putExtra(ProjectSettingsActivity.EXTRA_PROJECT_NAME, ((ProjectModel) adapter.getItem(which)).getName());
                            intent.putExtra(ProjectSettingsActivity.EXTRA_PROJECT_MODEL, (ProjectModel)adapter.getItem(which));
                            intent.putExtra(ProjectSettingsActivity.EXTRA_SHOW_FRAGMENT, "com.grappbox.grappbox.grappbox.ProjectSettingsActivity$GeneralPreferenceFragment");
                            startActivity(intent);
                        }
                    });
                    builder.show();
                    return false;
                }
            });
            btn.setImageDrawable(arrow_up);

        }
        else
        {
            navView.getMenu().clear();
            navView.inflateMenu(R.menu.activity_main_drawer);
            btn.setImageDrawable(arrow_down);
        }
    }

    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        _toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(_toolbar);
        changeToolbarTitle("Grappbox");
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
        String name = SessionAdapter.getInstance().getFisrname() + " " + SessionAdapter.getInstance().getLastname();

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
                changeToolbarTitle("Cloud");
                _fragmentManager.beginTransaction().replace(R.id.content_frame, fragment).commit();
                return;
            }
        }
        if (savedInstanceState == null) {
            _fragmentManager = getSupportFragmentManager();
            _fragmentManager.beginTransaction().replace(R.id.content_frame, new DashboardFragment(), DashboardFragment.TAG).commit();

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
    }

    @Override
    public void onBackPressed() {

        DrawerLayout drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        int nbrFrag = getSupportFragmentManager().getBackStackEntryCount();

        Log.v("Count Fragment : ", String.valueOf(nbrFrag));
        if (drawer.isDrawerOpen(GravityCompat.START)) {
            drawer.closeDrawer(GravityCompat.START);
        } else if (nbrFrag > 0) {
            getSupportFragmentManager().popBackStack();
        } else {
            super.onBackPressed();
        }
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.main, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        int id = item.getItemId();

        if (id == R.id.action_settings) {
            return true;
        }

        if (id == R.id.action_profile) {
            Fragment fragment = new UserProfileFragment();
            changeToolbarTitle("UserProfile");
            _fragmentManager.beginTransaction().replace(R.id.content_frame, fragment).commit();
            //startActivity(new Intent(this, UserProfileActivity.class));
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
        switch (id){

            case R.id.nav_dashboard:
                fragment = new DashboardFragment();
                changeToolbarTitle("Dashboard");
                break;

            case R.id.nav_project:
                fragment = new ProjectListFragment();
                changeToolbarTitle("Project list");
                break;

            case R.id.nav_whiteboard:
                fragment = new WhiteboardListFragment();
                changeToolbarTitle("Whiteboard");
                break;

            case R.id.nav_calendar:
                fragment = new AgendaFragment();
                changeToolbarTitle("Calendar");
                break;

            case R.id.nav_Timeline:
                fragment = new TimelineFragment();
                changeToolbarTitle("Timeline");
                break;

            case R.id.nav_Cloud:
                fragment = new CloudExplorerFragment();
                changeToolbarTitle("Cloud");
                break;
            case R.id.nav_Bugtracker:
                fragment = new BugTrackerFragment();
                changeToolbarTitle("Bug Tracker - BugList");
                break;
            default:
                break;
        }
        if (fragment != null)
            _fragmentManager.beginTransaction().replace(R.id.content_frame, fragment).commit();
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
    }

    private void changeToolbarTitle(String title)
    {
        _toolbar.setTitle(title);
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
                APIConnectAdapter.getInstance().startConnection("accountadministration/logout/" + SessionAdapter.getInstance().getToken());
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
