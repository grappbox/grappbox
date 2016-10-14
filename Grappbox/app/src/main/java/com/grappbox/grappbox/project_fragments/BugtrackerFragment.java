package com.grappbox.grappbox.project_fragments;


import android.content.Context;
import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Trace;
import android.support.annotation.Nullable;
import android.support.design.widget.FloatingActionButton;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentPagerAdapter;
import android.support.v4.app.FragmentStatePagerAdapter;
import android.support.v4.view.AsyncLayoutInflater;
import android.support.v4.view.ViewPager;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ListView;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.BugListFragmentStatePagerAdapter;
import com.grappbox.grappbox.bugtracker_fragments.NewBugActivity;

import java.util.EmptyStackException;

/**
 * A simple {@link Fragment} subclass.
 */
public class BugtrackerFragment extends Fragment {
    private FragmentStatePagerAdapter mPagesAdapter;
    private ViewPager mViewPager;
    private FloatingActionButton fab;

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
        mPagesAdapter = new BugListFragmentStatePagerAdapter(getActivity(), getChildFragmentManager());
        mViewPager.setOffscreenPageLimit(2);
        mViewPager.setAdapter(mPagesAdapter);
        fab = (FloatingActionButton) v.findViewById(R.id.fab);
        fab.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent newBug = new Intent(getActivity(), NewBugActivity.class);
                newBug.setAction(NewBugActivity.ACTION_NEW);
                newBug.putExtra(NewBugActivity.EXTRA_PROJECT_ID, getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1));
                startActivity(newBug);
            }
        });
        return v;
    }

}
