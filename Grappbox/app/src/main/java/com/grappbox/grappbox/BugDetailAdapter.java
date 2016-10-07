package com.grappbox.grappbox;

import android.support.v7.widget.RecyclerView;
import android.view.ViewGroup;

import com.grappbox.grappbox.model.BugModel;

/**
 * Created by marc on 07/10/2016.
 */

public class BugDetailAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {
    private static final int TYPE_TAGS = 0;
    private static final int TYPE_ASSIGNEES = 1;
    private static final int TYPE_SUBTITLE = 2;
    private static final int TYPE_COMMENT = 3;
    private static final int TYPE_COMMENT_REPLY = 4;

    //TODO : List<Tags>, List<Assignee>, List<Comments>

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        return null;
    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {

    }

    @Override
    public int getItemCount() {
        return 0;
    }


}
