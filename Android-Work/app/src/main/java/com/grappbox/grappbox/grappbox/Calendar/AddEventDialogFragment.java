package com.grappbox.grappbox.grappbox.Calendar;

import android.app.DialogFragment;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.grappbox.R;

/**
 * Created by tan_f on 21/01/2016.
 */
public class AddEventDialogFragment extends DialogFragment {

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.dialog_add_event, container);
        getDialog().setTitle("Add event");

        return view;
    }

}