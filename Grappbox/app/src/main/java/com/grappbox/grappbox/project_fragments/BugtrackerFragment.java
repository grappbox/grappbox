package com.grappbox.grappbox.project_fragments;


import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.R;

/**
 * A simple {@link Fragment} subclass.
 */
public class BugtrackerFragment extends Fragment {


    public BugtrackerFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        return inflater.inflate(R.layout.fragment_bugtracker, container, false);
    }

}
