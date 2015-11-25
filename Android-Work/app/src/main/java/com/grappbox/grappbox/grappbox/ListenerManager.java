package com.grappbox.grappbox.grappbox;

import android.content.Intent;
import android.view.View;
import android.widget.AdapterView;
import android.util.Log;

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
            if (position == 0){
                Intent intent = new Intent(view.getContext(), DashboardActivity.class);
                view.getContext().startActivity(intent);
            }else if (position == 1){
                Intent intent = new Intent(view.getContext(), WhiteboardActivity.class);
                view.getContext().startActivity(intent);
            }else if (position == 2){

            }else if (position == 3){

            }
        }
    };
}
