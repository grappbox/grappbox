package com.grappbox.grappbox.project_fragments;


import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentPagerAdapter;
import android.support.v4.app.FragmentStatePagerAdapter;
import android.support.v4.view.ViewPager;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ListView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.BugListFragmentStatePagerAdapter;

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
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View v = inflater.inflate(R.layout.fragment_bugtracker, container, false);
        mViewPager = (ViewPager) v.findViewById(R.id.viewPager);
        mPagesAdapter = new BugListFragmentStatePagerAdapter(getActivity(), getActivity().getSupportFragmentManager());
        mViewPager.setAdapter(mPagesAdapter);

        return v;
    }

}
