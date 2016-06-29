package com.grappbox.grappbox.grappbox.Dashboard;

import android.content.DialogInterface;
import android.os.Bundle;

import android.support.design.widget.TabLayout;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentPagerAdapter;
import android.support.v4.view.ViewPager;
import android.support.v7.app.AlertDialog;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;


import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;
import java.util.List;
import java.util.Vector;

public class DashboardFragment extends Fragment {

    public static final String TAG = DashboardFragment.class.getSimpleName();
    private SectionsPagerAdapter    _SectionPagerAdapter;
    private ImageButton             _configTabButton;
    private ViewPager               _viewPager;
    private TabLayout               _tabLayout;

    @Override
    public void onCreate(Bundle savedInstanceBundle)
    {
        super.onCreate(savedInstanceBundle);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View rootView = inflater.inflate(R.layout.fragment_dashboard, container, false);

        List<Fragment> fragment = new Vector<Fragment>();
        fragment.add(Fragment.instantiate(getActivity(), TeamOccupationFragment.class.getName()));
        fragment.add(Fragment.instantiate(getActivity(), NextMeetingFragment.class.getName()));
        fragment.add(Fragment.instantiate(getActivity(), GlobalProgressFragment.class.getName()));

        _SectionPagerAdapter = new SectionsPagerAdapter(super.getChildFragmentManager(), fragment);
        _configTabButton = (ImageButton) rootView.findViewById(R.id.configTabButton);
        _SectionPagerAdapter.AddTabTitle("Team Occupation");
        _SectionPagerAdapter.AddTabTitle("Next Meeting");
        _SectionPagerAdapter.AddTabTitle("Global Progress");
        _viewPager = (ViewPager)rootView.findViewById(R.id.pager);
        _viewPager.setAdapter(_SectionPagerAdapter);
        _tabLayout = (TabLayout) rootView.findViewById(R.id.sliding_tabs);
        _tabLayout.setupWithViewPager(_viewPager);
        _configTabButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
                builder.setTitle("Dashboard");
                builder.setMultiChoiceItems(R.array.dashboard_tab_bar_title, null, new DialogInterface.OnMultiChoiceClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which, boolean isChecked) {

                    }
                });
                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {

                    }
                });
                builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {

                    }
                });
                builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {

                    }
                });
                builder.show();
            }
        });

        return rootView;
    }


    private class SectionsPagerAdapter extends FragmentPagerAdapter {

        private List<Fragment>  _Fragments = null;
        private List<String> _TabTitle = new ArrayList<>();

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
            return _TabTitle.get(position);
        }

        public void AddTabTitle(String title)
        {
            _TabTitle.add(title);
        }
    }
}
