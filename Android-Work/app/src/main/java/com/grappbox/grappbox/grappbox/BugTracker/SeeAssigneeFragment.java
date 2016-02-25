package com.grappbox.grappbox.grappbox.BugTracker;


import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.util.Log;
import android.util.Pair;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.LinearLayout;

import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;
import java.util.Objects;

/**
 * A simple {@link Fragment} subclass.
 */
public class SeeAssigneeFragment extends Fragment {
    private BugEntity _bug;

    public SeeAssigneeFragment() {
        // Required empty public constructor
    }

    public void InitCheckboxes()
    {
        View v = getView();
        assert v != null;
        LinearLayout lay = (LinearLayout) v.findViewById(R.id.assignee_container);

        for (int i = 0; i < lay.getChildCount(); ++i)
        {
            LinearLayout current_lay = (LinearLayout) lay.getChildAt(i);
            BugIdCheckbox current = (BugIdCheckbox) current_lay.findViewById(R.id.cb_assigned);

            for (UserEntity user : _bug.GetUsers())
            {
                if (Objects.equals(user.GetId(), current.GetStoredId()))
                {
                    current.setChecked(true);
                    break;
                }
            }
        }
    }

    public List<Pair<String, Boolean>> DiffIds()
    {
        List<Pair<String, Boolean>> idView = new ArrayList<>();
        View v = getView();
        assert v != null;
        LinearLayout lay = (LinearLayout) v.findViewById(R.id.assignee_container);

        for (int i = 0; i < lay.getChildCount(); ++i)
        {
            LinearLayout current_lay = (LinearLayout) lay.getChildAt(i);
            BugIdCheckbox current = (BugIdCheckbox) current_lay.findViewById(R.id.cb_assigned);
            boolean isSet = false;
            Pair<String, Boolean> possibleAdd = new Pair<>("", true);
            for (UserEntity user : _bug.GetUsers())
            {
                if (Objects.equals(user.GetId(), current.GetStoredId()))
                {
                    isSet = true;
                    if (!current.isChecked())
                        idView.add(new Pair<>(current.GetStoredId(), false));
                    break;
                }
            }
            if (!isSet && current.isChecked()) {
                idView.add(new Pair<>(current.GetStoredId(), true));
            }
        }
        return idView;
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View v = inflater.inflate(R.layout.fragment_see_assignee, container, false);
        Button btnSave = (Button) v.findViewById(R.id.btn_save);
        if (!(getActivity() instanceof EditBugActivity)) {
            getActivity().onBackPressed();
            return v;
        }
        _bug = ((EditBugActivity) getActivity()).GetModel();
        GetProjectUserTask task = new GetProjectUserTask(getActivity(), (LinearLayout) v.findViewById(R.id.assignee_container), new OnTaskListener() {
            @Override
            public void OnTaskEnd(boolean isErrorOccured, String... params) {
                InitCheckboxes();
            }
        });
        task.execute();

        btnSave.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                List<Pair<String, Boolean>> rmAndAdd = DiffIds();

                SetParticipantTask task = new SetParticipantTask(getActivity(), new OnTaskListener() {
                    @Override
                    public void OnTaskEnd(boolean isErrorOccured, String... params) {
                        if (isErrorOccured || params.length < 1)
                            return;
                        try {
                            JSONObject data = new JSONObject(params[0]);

                            ((EditBugActivity) getActivity()).RefreshBug();
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }
                        getActivity().onBackPressed();
                    }
                }, rmAndAdd);
                task.execute(_bug.GetId());
            }
        });
        return v;
    }

}
