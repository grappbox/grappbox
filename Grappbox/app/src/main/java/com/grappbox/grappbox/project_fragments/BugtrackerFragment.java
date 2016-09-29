package com.grappbox.grappbox.project_fragments;


import android.content.Context;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Trace;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentPagerAdapter;
import android.support.v4.app.FragmentStatePagerAdapter;
import android.support.v4.view.AsyncLayoutInflater;
import android.support.v4.view.ViewPager;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ListView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.BugListFragmentStatePagerAdapter;

import java.util.EmptyStackException;

/**
 * A simple {@link Fragment} subclass.
 */
public class BugtrackerFragment extends Fragment {
    private FragmentStatePagerAdapter mPagesAdapter;
    private ViewPager mViewPager;

    public BugtrackerFragment() {
        // Required empty public constructor
    }

    @Override
    public void onActivityCreated(@Nullable Bundle savedInstanceState) {
        super.onActivityCreated(savedInstanceState);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View v = inflater.inflate(R.layout.fragment_bugtracker, container, false);
        mViewPager = (ViewPager) v.findViewById(R.id.viewPager);
        mPagesAdapter = new BugListFragmentStatePagerAdapter(getActivity(), getFragmentManager());
        mViewPager.setOffscreenPageLimit(2);
        mViewPager.setAdapter(mPagesAdapter);
        return v;
    }

}
