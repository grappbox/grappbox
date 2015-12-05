package com.grappbox.grappbox.grappbox;

import android.content.ContentValues;
import android.os.AsyncTask;
import android.os.Bundle;
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

    DrawerLayout _Drawer;
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        _toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(_toolbar);

        _Drawer = (DrawerLayout) findViewById(R.id.drawer_layout);
        ActionBarDrawerToggle toggle = new ActionBarDrawerToggle(
                this, _Drawer, _toolbar, R.string.navigation_drawer_open, R.string.navigation_drawer_close);
        _Drawer.setDrawerListener(toggle);
        toggle.syncState();
        getSupportActionBar().setTitle("Dashboard");

        NavigationView navigationView = (NavigationView) findViewById(R.id.nav_view);
        navigationView.setNavigationItemSelectedListener(this);

        View headerView = navigationView.getHeaderView(0);
        TextView text = (TextView)headerView.findViewById(R.id.nav_head_name_user);
        String name = SessionAdapter.getInstance().getFisrname() + " " + SessionAdapter.getInstance().getLastname();
        text.setText(name);
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
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
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

        if (id == R.id.nav_dashboard) {
            getSupportFragmentManager().beginTransaction().replace(R.id.content_frame, new DashboardFragment(), DashboardFragment.TAG).commit();
        } else if (id == R.id.nav_whiteboard) {

            getSupportFragmentManager().beginTransaction().replace(R.id.content_frame, new WhiteboardListFragment(), DashboardFragment.TAG).commit();
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
