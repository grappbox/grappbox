package com.grappbox.grappbox.project_fragments;


import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.github.mikephil.charting.charts.LineChart;
import com.github.mikephil.charting.data.Entry;
import com.github.mikephil.charting.data.LineData;
import com.github.mikephil.charting.data.LineDataSet;
import com.grappbox.grappbox.R;

import java.util.ArrayList;
import java.util.List;

/**
 * A simple {@link Fragment} subclass.
 */
public class DashboardFragment extends Fragment {


    public DashboardFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View v = inflater.inflate(R.layout.fragment_dashboard, container, false);
        LineChart chart = (LineChart) v.findViewById(R.id.chart);
        List<Entry> entries = new ArrayList<Entry>();
        entries.add(new Entry(0, 5));
        entries.add(new Entry(1, 7));
        entries.add(new Entry(2f, 12.5f));
        entries.add(new Entry(3, 3));
        entries.add(new Entry(4, 18));
        entries.add(new Entry(5, 20));
        LineDataSet dataSet = new LineDataSet(entries, "Line bar chart test");
        LineData lineData = new LineData(dataSet);
        chart.setData(lineData);
        chart.invalidate();
        return v;
    }

}
