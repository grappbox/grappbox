package com.grappbox.grappbox.project_fragments;


import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentStatePagerAdapter;
import android.support.v4.view.ViewPager;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.github.mikephil.charting.charts.LineChart;
import com.github.mikephil.charting.data.Entry;
import com.github.mikephil.charting.data.LineData;
import com.github.mikephil.charting.data.LineDataSet;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.DashboardStatePagerAdapter;

import java.util.ArrayList;
import java.util.List;

/**
 * A simple {@link Fragment} subclass.
 */
public class DashboardFragment extends Fragment {

    private static final String LOG_TAG = DashboardFragment.class.getSimpleName();

    private FragmentStatePagerAdapter mPagesAdapter;
    private ViewPager mViewPager;

    public DashboardFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View v = inflater.inflate(R.layout.fragment_dashboard, container, false);
        mViewPager = (ViewPager)v.findViewById(R.id.viewPager);
        mPagesAdapter = new DashboardStatePagerAdapter(getActivity(), getActivity().getSupportFragmentManager());
        mViewPager.setAdapter(mPagesAdapter);
        mViewPager.setOffscreenPageLimit(1);
        return v;
    }

}
