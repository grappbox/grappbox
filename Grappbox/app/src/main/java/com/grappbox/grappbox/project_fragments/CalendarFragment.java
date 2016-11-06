package com.grappbox.grappbox.project_fragments;


import android.content.Intent;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.v4.app.Fragment;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CalendarView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.calendar_fragment.NewEventActivity;

public class CalendarFragment extends Fragment {

    private CalendarView mCalendar;
    private RecyclerView mRecyclerView;
    private FloatingActionButton    mAddEvent;

    public CalendarFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_calendar, container, false);

        mRecyclerView = (RecyclerView) v.findViewById(R.id.agendalist);
        mCalendar = (CalendarView)v.findViewById(R.id.calendarview);
        mAddEvent = (FloatingActionButton) v.findViewById(R.id.fab);
        mAddEvent.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent newEvent = new Intent(getContext(), NewEventActivity.class);
                getContext().startActivity(newEvent);
            }
        });

        return v;
    }

}
