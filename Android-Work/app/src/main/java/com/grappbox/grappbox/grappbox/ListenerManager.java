package com.grappbox.grappbox.grappbox;

import android.view.View;
import android.widget.AdapterView;

/**
 * Created by Arkanice on 18/09/2015.
 */
public class ListenerManager
{
    private static ListenerManager _instance = null;

    private ListenerManager()
    {

    }

    public static ListenerManager getInstance()
    {
        if (_instance == null)
        {
            _instance = new ListenerManager();
        }
        return _instance;
    }

    public AdapterView.OnItemClickListener GetNavigationListener()
    {
        return (NavigationListener);
    }

    private AdapterView.OnItemClickListener NavigationListener = new AdapterView.OnItemClickListener()
    {
        @Override
        public void onItemClick(AdapterView<?> parent, View view, int position, long id)
        {

        }
    };
}
