package com.grappbox.grappbox.grappbox;

import android.content.Intent;
import android.content.res.Configuration;
import android.os.AsyncTask;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
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
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.Calendar.AgendaFragment;
import com.grappbox.grappbox.grappbox.Cloud.CloudExplorerFragment;
import com.grappbox.grappbox.grappbox.Dashboard.DashboardFragment;
import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.Settings.UserProfileFragment;
import com.grappbox.grappbox.grappbox.Whiteboard.WhiteboardListFragment;

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
    private Toolbar _toolbar;
    private FragmentManager _fragmentManager;
    private ActionBarDrawerToggle _actionBarDrawerToggle;
    private DrawerLayout _Drawer;

    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        _toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(_toolbar);
        changeToolbarTitle("Grappbox");

        _Drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        _actionBarDrawerToggle = new ActionBarDrawerToggle(
                this, _Drawer, _toolbar, R.string.navigation_drawer_open, R.string.navigation_drawer_close);
        _Drawer.setDrawerListener(_actionBarDrawerToggle);
        _actionBarDrawerToggle.syncState();
        _actionBarDrawerToggle.setDrawerIndicatorEnabled(true);
        NavigationView navigationView = (NavigationView) findViewById(R.id.nav_view);
        navigationView.setNavigationItemSelectedListener(this);

        View headerView = navigationView.getHeaderView(0);
        TextView text = (TextView)headerView.findViewById(R.id.nav_head_name_user);
        String name = SessionAdapter.getInstance().getFisrname() + " " + SessionAdapter.getInstance().getLastname();
        text.setText(name);
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
            case R.id.nav_Cloud:
                fragment = new CloudExplorerFragment();
                changeToolbarTitle("Cloud");
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
