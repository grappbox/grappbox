package com.grappbox.grappbox.grappbox.BugTracker;


import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.util.Pair;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.LinearLayout;

import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;
import java.util.List;
import java.util.Objects;

/**
 * A simple {@link Fragment} subclass.
 */
public class SeeCategoryFragment extends Fragment {

    public SeeCategoryFragment() {
        // Required empty public constructor
    }

    public void InitCheckboxes()
    {
        View v = getView();
        assert v != null;
        LinearLayout lay = (LinearLayout) v.findViewById(R.id.category_container);
        BugEntity _bug = ((EditBugActivity) getActivity()).GetModel();

        for (int i = 0; i < lay.getChildCount(); ++i)
        {
            LinearLayout current_lay = (LinearLayout) lay.getChildAt(i);
            BugIdCheckbox current = (BugIdCheckbox) current_lay.findViewById(R.id.cb_assigned);

            for (TagEntity user : _bug.GetTags())
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
        BugEntity _bug = ((EditBugActivity) getActivity()).GetModel();
        View v = getView();
        assert v != null;
        LinearLayout lay = (LinearLayout) v.findViewById(R.id.category_container);

        for (int i = 0; i < lay.getChildCount(); ++i)
        {
            LinearLayout current_lay = (LinearLayout) lay.getChildAt(i);
            BugIdCheckbox current = (BugIdCheckbox) current_lay.findViewById(R.id.cb_assigned);
            boolean isSet = false;
            Pair<String, Boolean> possibleAdd = new Pair<>("", true);
            for (TagEntity user : _bug.GetTags())
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
        View v = inflater.inflate(R.layout.fragment_see_category, container, false);

        if (!(getActivity() instanceof EditBugActivity)){
            getActivity().onBackPressed();
            return v;
        }
        Button btn_save = (Button) v.findViewById(R.id.btn_save);
        GetBugTagTask task = new GetBugTagTask(getActivity(), (LinearLayout) v.findViewById(R.id.category_container), new OnTaskListener() {
            @Override
            public void OnTaskEnd(boolean isErrorOccured, String... params) {
                InitCheckboxes();
            }
        });
        task.execute();

        btn_save.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                List<Pair<String, Boolean>> rmAndAdd = DiffIds();

                AssignBatchTagTask task = new AssignBatchTagTask(getActivity(), new OnTaskListener() {
                    @Override
                    public void OnTaskEnd(boolean isErrorOccured, String... params) {
                        ((EditBugActivity) getActivity()).RefreshBug();
                        getActivity().onBackPressed();
                    }
                }, rmAndAdd);
                task.execute(((EditBugActivity)getActivity()).GetModel().GetId());
            }
        });
        return v;
    }

}
