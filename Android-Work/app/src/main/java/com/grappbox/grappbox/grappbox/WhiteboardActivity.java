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
import android.widget.ImageView;
import android.widget.ListView;

public class WhiteboardActivity extends AppCompatActivity {

    private ListView _DrawerList;
    private ActionBarDrawerToggle _DrawerToggle;
    private DrawerLayout _DrawerLayout;
    private ImageView _DrawerImage;
    private ArrayAdapter<String> _Adapter;


    private String _ActivityTitle;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_whiteboard);

        initializeNavigationDrawer();
    }

    private void initializeNavigationDrawer()
    {
        _DrawerLayout = (DrawerLayout)findViewById(R.id.dashboardDrawerLayout);
        _DrawerList = (ListView)findViewById(R.id.navList);
        _ActivityTitle = getTitle().toString();
        _DrawerImage = (ImageView)findViewById(R.id.profile_photo);

        addDrawerItem();
        setupDrawer();

        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setHomeButtonEnabled(true);
        getSupportActionBar().setTitle("Whiteboard");
        _DrawerImage.setImageResource(R.drawable.allyriane_launois);
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

            @Override
            public void onDrawerOpened(View view)
            {
                super.onDrawerOpened(view);
                getSupportActionBar().setTitle("Grappbox");
                invalidateOptionsMenu();
            }

            @Override
            public void onDrawerClosed(View view)
            {
                super.onDrawerClosed(view);
                getSupportActionBar().setTitle("Whiteboard");
                invalidateOptionsMenu();
            }
        };

        _DrawerToggle.setDrawerIndicatorEnabled(true);
        _DrawerLayout.setDrawerListener(_DrawerToggle);
    }
}
