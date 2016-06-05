package com.grappbox.grappbox.grappbox.BugTracker;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.support.design.widget.TabLayout;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentPagerAdapter;
import android.support.v4.view.ViewPager;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;

import com.grappbox.grappbox.grappbox.R;

import java.util.List;
import java.util.Objects;
import java.util.Vector;

public class BugTrackerFragment extends Fragment {
    private SectionsPagerAdapter _SectionPagerAdapter;
    private ViewPager _viewPager;
    private TabLayout _tabLayout;


    public BugTrackerFragment() {
        // Required empty public constructor
    }

    public static BugTrackerFragment newInstance() {
        BugTrackerFragment fragment = new BugTrackerFragment();
        Bundle args = new Bundle();
        return fragment;
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
    }

    public void RefreshClosedList()
    {
        for (Fragment fragment : _SectionPagerAdapter._Fragments)
        {
            if (Objects.equals(fragment.getClass().getName(), BugClosedListFragment.class.getName()))
            {
                BugClosedListFragment closedBugFragment = (BugClosedListFragment) fragment;
                closedBugFragment.refresher.onRefresh();
            }
        }
    }

    public void RefreshOpenList()
    {
        for (Fragment fragment : _SectionPagerAdapter._Fragments)
        {
            if (Objects.equals(fragment.getClass().getName(), BugOpenListFragment.class.getName()))
            {
                BugOpenListFragment openBugFragment = (BugOpenListFragment) fragment;
                openBugFragment.refresher.onRefresh();
            }
        }
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View v = inflater.inflate(R.layout.fragment_bug_tracker, container, false);
        List<Fragment> pages = new Vector<>();
        Button btnNew = (Button) v.findViewById(R.id.btn_new_issue);

        pages.add(((BugOpenListFragment)Fragment.instantiate(getActivity(), BugOpenListFragment.class.getName())).SetParent(this));
        pages.add(((BugClosedListFragment)Fragment.instantiate(getActivity(), BugClosedListFragment.class.getName())).SetParent(this));
        pages.add(((BugYoursListFragment)Fragment.instantiate(getActivity(), BugYoursListFragment.class.getName())).SetParent(this));

        _SectionPagerAdapter = new SectionsPagerAdapter(super.getChildFragmentManager(), pages);
        _viewPager = (ViewPager)v.findViewById(R.id.pager);
        _viewPager.setAdapter(_SectionPagerAdapter);
        _tabLayout = (TabLayout) v.findViewById(R.id.sliding_tabs);
        _tabLayout.setupWithViewPager(_viewPager);

        btnNew.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(getContext(), BugCreationActivity.class);
                startActivity(intent);
            }
        });
        return v;
    }

    private class SectionsPagerAdapter extends FragmentPagerAdapter {

        private List<Fragment>  _Fragments = null;
        private String _TabTitle[] = getResources().getStringArray(R.array.bugtracker_list_tabs);

        public SectionsPagerAdapter(FragmentManager fm, List<Fragment> fragments) {
            super(fm);
            _Fragments = fragments;
        }

        @Override
        public Fragment getItem(int position) {
            return _Fragments.get(position);
        }

        @Override
        public int getCount() {
            return _Fragments.size();
        }

        @Override
        public CharSequence getPageTitle(int position) {
            return _TabTitle[position];
        }
    }
}
