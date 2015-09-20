package com.grappbox.grappbox.grappbox;

import android.content.res.Configuration;
import android.support.v4.app.Fragment;
import android.support.v4.view.PagerAdapter;
import android.support.v4.view.ViewPager;
import android.support.v4.widget.DrawerLayout;
import android.support.v7.app.ActionBarDrawerToggle;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.ListView;

import java.util.List;
import java.util.Vector;

public class DashboardActivity extends AppCompatActivity {

    private ListView _DrawerList;
    private ActionBarDrawerToggle _DrawerToggle;
    private DrawerLayout _DrawerLayout;
    private ArrayAdapter<String> _Adapter;

    private PagerAdapter _PagerAdapter;

    private String _ActivityTitle;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_dashboard);

        List<Fragment> fragments = new Vector<Fragment>();

        _DrawerList = (ListView)findViewById(R.id.navList);
        _DrawerLayout = (DrawerLayout)findViewById(R.id.dashboardDrawerLayout);
        _ActivityTitle = getTitle().toString();

        addDrawerItem();
        setupDrawer();

        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setHomeButtonEnabled(true);

        fragments.add(Fragment.instantiate(this, TeamOccupationFragment.class.getName()));
        fragments.add(Fragment.instantiate(this, NextMeetingFragment.class.getName()));
        fragments.add(Fragment.instantiate(this, GlobalProgressFragment.class.getName()));

        this._PagerAdapter = new GrappboxPagerAdapter(super.getSupportFragmentManager(), fragments);
        ViewPager pager = (ViewPager)findViewById(R.id.viewpager);
        pager.setAdapter(_PagerAdapter);
        getSupportActionBar().setTitle("Dashboard");
    }

    @Override
    protected void onPostCreate(Bundle savedInstanceState)
    {
        super.onPostCreate(savedInstanceState);
        _DrawerToggle.syncState();
    }

    @Override
    public void onConfigurationChanged(Configuration newConfig)
    {
        super.onConfigurationChanged(newConfig);
        _DrawerToggle.onConfigurationChanged(newConfig);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_dashboard, menu);
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

        if (_DrawerToggle.onOptionsItemSelected(item)) {
            return true;
        }

        return super.onOptionsItemSelected(item);
    }

    private void addDrawerItem()
    {
        String navigationArray[] = getResources().getStringArray(R.array.MenuComponent);
        _Adapter = new ArrayAdapter<String>(this, android.R.layout.simple_list_item_1, navigationArray);
        _DrawerList.setAdapter(_Adapter);
        _DrawerList.setOnItemClickListener(ListenerManager.getInstance().GetNavigationListener());
    }

    private void setupDrawer()
    {
        _DrawerToggle = new ActionBarDrawerToggle(this, _DrawerLayout, R.string.drawer_open, R.string.drawer_close) {

            public void onDrawerOpened(View view)
            {
                super.onDrawerOpened(view);
                getSupportActionBar().setTitle("Grappbox");
                invalidateOptionsMenu();
            }

            public void onDrawerClosed(View view)
            {
                super.onDrawerClosed(view);
                getSupportActionBar().setTitle("Dashboard");
                invalidateOptionsMenu();
            }
        };

        _DrawerToggle.setDrawerIndicatorEnabled(true);
        _DrawerLayout.setDrawerListener(_DrawerToggle);
    }
}
