package com.grappbox.grappbox.statistic_fragment;

import android.os.Bundle;
import android.support.annotation.Nullable;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.dashboard_fragment.AbstractDashboard;

/**
 * Created by tan_f on 22/12/2016.
 */

public class StatisticListFragment extends AbstractDashboard {

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_list_stat_chart, container, false);
        return v;
    }
}
