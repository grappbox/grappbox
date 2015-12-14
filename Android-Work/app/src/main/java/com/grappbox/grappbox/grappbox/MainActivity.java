package com.grappbox.grappbox.grappbox;

import android.support.v4.app.FragmentTransaction;
import android.content.ContentValues;
import android.content.res.Configuration;
import android.os.AsyncTask;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v7.app.ActionBar;
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
import org.json.JSONException;
import org.json.JSONObject;
import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.List;

public class MainActivity extends AppCompatActivity
        implements NavigationView.OnNavigationItemSelectedListener {

    private static final String TAG = MainActivity.class.getSimpleName();
    private Toolbar _toolbar;
    private FragmentManager _fragmentManager;
    private ActionBarDrawerToggle _actionBarDrawerToggle;

    DrawerLayout _Drawer;
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
        _fragmentManager = getSupportFragmentManager();
        _fragmentManager.beginTransaction().replace(R.id.content_frame, new DashboardFragment(), DashboardFragment.TAG).commit();
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
        if (drawer.isDrawerOpen(GravityCompat.START)) {
            drawer.closeDrawer(GravityCompat.START);
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

            case R.id.nav_whiteboard:
                fragment = new WhiteboardListFragment();
                changeToolbarTitle("Whiteboard");
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
