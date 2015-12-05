package com.grappbox.grappbox.grappbox;


import android.content.ContentValues;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ListView;
import android.widget.SimpleAdapter;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;


/**
 * A simple {@link Fragment} subclass.
 */
public class WhiteboardListFragment extends Fragment {

    private View _view;
    private ListView _ListWhiteboard;

    public WhiteboardListFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        _view = inflater.inflate(R.layout.fragment_whiteboard_list, container, false);
        createContentView();
        return _view;
    }

    private void createContentView()
    {
        _ListWhiteboard = (ListView)_view.findViewById(R.id.list_whiteboard);
        ArrayList<HashMap<String, String>> listNextMeeting = new ArrayList<HashMap<String, String>>();

        HashMap<String, String> map = new HashMap<String, String>();
        map.put("whiteboard_title", "Game Sphere");
        map.put("whiteboard_project_name", "Ninvento");
        listNextMeeting.add(map);

        map = new HashMap<String, String>();
        map.put("whiteboard_title", "Test");
        map.put("whiteboard_project_name", "Company");
        listNextMeeting.add(map);

        SimpleAdapter meetingAdapter = new SimpleAdapter(_view.getContext(), listNextMeeting, R.layout.item_list_whiteboard,
                new String[] {"whiteboard_title", "project_name", },
                new int[] {R.id.whiteboard_title, R.id.whiteboard_project_name, });
        _ListWhiteboard.setAdapter(meetingAdapter);
    }
}
