package com.grappbox.grappbox.bugtracker_fragments;


import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.R;

/**
 * A simple {@link Fragment} subclass.
 */
public class BugDetailsFragment extends Fragment {


    public BugDetailsFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.bugtracker_details, container, false);
        return v;
    }

}
