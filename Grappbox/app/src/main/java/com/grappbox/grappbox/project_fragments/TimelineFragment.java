package com.grappbox.grappbox.project_fragments;


import android.accounts.AccountManager;
import android.content.DialogInterface;
import android.content.Intent;
import android.database.Cursor;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.design.widget.FloatingActionButton;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentStatePagerAdapter;
import android.support.v4.view.ViewPager;
import android.support.v7.app.AlertDialog;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.TimelineFragmentStatePagerAdapter;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

/**
 * A simple {@link Fragment} subclass.
 */
public class TimelineFragment extends Fragment {

    private static final String LOG_TAG = TimelineFragment.class.getSimpleName();

    private FragmentStatePagerAdapter mPagesAdapter;
    private ViewPager mViewPager;

    public TimelineFragment() {
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
        View v = inflater.inflate(R.layout.fragment_timeline, container, false);
        mViewPager = (ViewPager) v.findViewById(R.id.viewPager);
        mPagesAdapter = new TimelineFragmentStatePagerAdapter(getActivity(), getActivity().getSupportFragmentManager());
        mViewPager.setAdapter(mPagesAdapter);
        mViewPager.setOffscreenPageLimit(2);
        return v;
    }

}
