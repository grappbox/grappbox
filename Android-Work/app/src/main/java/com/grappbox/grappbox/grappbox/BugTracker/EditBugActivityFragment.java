package com.grappbox.grappbox.grappbox.BugTracker;

import android.support.v4.app.Fragment;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.grappbox.R;

/**
 * A placeholder fragment containing a simple view.
 */
public class EditBugActivityFragment extends Fragment {

    public EditBugActivityFragment() {
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_edit_bug, container, false);
    }
}
