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
    private Fragment _activeFrag;
    private FragmentManager _fragmentManager;
    private ActionBarDrawerToggle _actionBarDrawerToggle;

    DrawerLayout _Drawer;
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        _toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(_toolbar);
        changeToolbarTitle("Dashboard");

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
        _activeFrag = new DashboardFragment();
        _fragmentManager = getSupportFragmentManager();
        _fragmentManager.beginTransaction().add(R.id.content_frame, _activeFrag, DashboardFragment.TAG).commit();
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
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.main, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        int id = item.getItemId();

        //noinspection SimplifiableIfStatement
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
        // Handle navigation view item clicks here.
        int id = item.getItemId();

        FragmentTransaction transaction;
        if (id == R.id.nav_dashboard) {
            transaction = _fragmentManager.beginTransaction();
            transaction.remove(_activeFrag);
            changeToolbarTitle("Dashboard");
            _activeFrag = new DashboardFragment();
            transaction.add(R.id.content_frame, _activeFrag, _activeFrag.getTag());
            transaction.addToBackStack(null);
            transaction.commit();
        } else if (id == R.id.nav_whiteboard) {
            transaction = _fragmentManager.beginTransaction();
            transaction.remove(_activeFrag);
            changeToolbarTitle("Whiteboard");
            _activeFrag = new WhiteboardListFragment();
            transaction.add(R.id.content_frame, _activeFrag, _activeFrag.getTag());
            transaction.addToBackStack(null);
            transaction.commit();
        } else if (id == R.id.nav_Timeline) {

        }

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
        setSupportActionBar(_toolbar);
    }

    public class APIRequestLogout extends AsyncTask<String, Void, Void> {

        private static final String _API_URL_BASE = "http://api.grappbox.com/app_dev.php/";

        private ContentValues  getLoginDataFromJSON(String resultJSON) throws JSONException
        {
            final String DATA_LIST = "user";
            final String[] DATA_USER = {"id", "firstname", "lastname", "email", "token"};

            ContentValues JSONContent = new ContentValues();
            JSONObject jsonObject = new JSONObject(resultJSON);
            JSONObject userData = jsonObject.getJSONObject(DATA_LIST);

            for (String data : DATA_USER) {
                if (userData.getString(data) == null)
                    return null;
                JSONContent.put(data, userData.getString(data));
            }

            return JSONContent;
        }

        @Override
        protected void onPostExecute(Void result) {
            super.onPostExecute(result);
            logoutUser();
        }

        @Override
        protected Void doInBackground(String ... param)
        {
            HttpURLConnection connection = null;
            BufferedReader reader = null;
            ContentValues contentAPI = null;
            String resultAPI;
            List<ContentValues> listResult = null;

            try {
                String urlPath = "http://api.grappbox.com/app_dev.php/V0.8/accountadministration/logout/" + SessionAdapter.getInstance().getToken();
                URL url = new URL(urlPath);
                connection = (HttpURLConnection)url.openConnection();
                connection.setRequestMethod("GET");
                connection.connect();

                InputStream inputStream = connection.getInputStream();
                StringBuffer buffer = new StringBuffer();
                if (inputStream == null) {
                    return null;
                }
                reader = new BufferedReader(new InputStreamReader(inputStream));

                String line;
                String nLine;
                while ((line = reader.readLine()) != null) {
                    nLine = line + "\n";
                    buffer.append(nLine);
                }

                if (buffer.length() == 0) {
                    return null;
                }

                resultAPI = buffer.toString();

            } catch (IOException e){
                Log.e("APIConnection", "Error ", e);
                return null;
            } finally {
                if (connection != null){
                    connection.disconnect();
                }
                if (reader != null){
                    try {
                        reader.close();
                    } catch (final IOException e){
                        Log.e("APIConnection", "Error ", e);
                    }
                }
            }
            return null;
        }

    }
}
