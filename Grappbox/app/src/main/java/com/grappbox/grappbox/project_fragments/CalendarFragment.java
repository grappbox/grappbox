package com.grappbox.grappbox.project_fragments;


import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.CalendarView;
import android.widget.ImageView;
import android.widget.ListView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.interfaces.CalendarPickerController;
import com.grappbox.grappbox.singleton.CalendarManager;

public class CalendarFragment extends Fragment {

    private ListView    mListView;
    private CalendarPickerController mCalendarPickerController;

    public CalendarFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_calendar, container, false);

        mListView = (ListView) v.findViewById(R.id.agenda_listview);
        mListView.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                mCalendarPickerController.onEventSelected(CalendarManager.getInstance().getEvents().get(position));
            }
        });

        return v;
    }

}
