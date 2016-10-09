package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.content.res.ColorStateList;
import android.graphics.Color;
import android.os.AsyncTask;
import android.os.Build;
import android.support.design.widget.TextInputEditText;
import android.support.v4.util.Pair;
import android.support.v4.view.AsyncLayoutInflater;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.grappbox.grappbox.BuildConfig;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.BugCommentModel;
import com.grappbox.grappbox.model.BugModel;
import com.grappbox.grappbox.model.BugTagModel;
import com.grappbox.grappbox.model.UserModel;

import java.util.ArrayList;
import java.util.Collection;
import java.util.HashSet;
import java.util.List;

/**
 * Created by marc on 07/10/2016.
 */

public class BugDetailAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {
    private static final int TYPE_TAGS = 0;
    private static final int TYPE_ASSIGNEES = 1;
    private static final int TYPE_SUBTITLE = 2;
    private static final int TYPE_COMMENT = 3;
    private static final int TYPE_COMMENT_REPLY = 4;

    private List<BugTagModel> mTags;
    private List<UserModel> mAssignees;
    private List<BugCommentModel> mComments;
    private BugModel mBug;

    private LayoutInflater mInflater;
    private Context mContext;

    public BugDetailAdapter(Context context) {
        mContext = context;
        mInflater = LayoutInflater.from(context);
        mTags = new ArrayList<>();
        mAssignees = new ArrayList<>();
        mComments = new ArrayList<>();
    }

    public void setBugModel(BugModel model){
        mBug = model;
        mTags = model.tags;
        mAssignees = model.assignees;
        mComments = model.comments;
        notifyDataSetChanged();
    }

    private RecyclerView.ViewHolder createTagsViewHolder(ViewGroup parent){
        RecyclerView.ViewHolder vh = new HorizontalHolder(mInflater.inflate(R.layout.list_item_bug_tags, parent, false));
        return vh;
    }

    private RecyclerView.ViewHolder createAssigneesViewHolder(ViewGroup parent){
        RecyclerView.ViewHolder vh = new HorizontalHolder(mInflater.inflate(R.layout.list_item_bug_assignees, parent, false));
        return vh;
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        switch (viewType){
            case TYPE_TAGS:
                return createTagsViewHolder(parent);
            case TYPE_ASSIGNEES:
                return createAssigneesViewHolder(parent);
            case TYPE_SUBTITLE:
                return new SubtitleHolder(mInflater.inflate(R.layout.list_item_custom_title, parent, false));
            case TYPE_COMMENT:
                return new CommentHolder(mInflater.inflate(R.layout.list_item_bug_comment, parent, false));
            case TYPE_COMMENT_REPLY:
                return new CommentReplyHolder(mInflater.inflate(R.layout.list_item_bug_comment_reply, parent, false));
            default:
                throw new IllegalArgumentException("Bad viewType : " + viewType);
        }
    }

    @Override
    public int getItemViewType(int position) {
        switch (position){
            case TYPE_TAGS:
            case TYPE_ASSIGNEES:
            case TYPE_SUBTITLE:
                return position;
            default:
                if (position == getItemCount() - 1)
                    return TYPE_COMMENT_REPLY;
                return TYPE_COMMENT;
        }
    }

    private void bindTags(HorizontalHolder holder){
        if (mTags.isEmpty()){
            holder.emptycontainer.setVisibility(View.VISIBLE);
        } else {
            new LazyTagLoading(mContext, holder, holder.container).execute(mTags.toArray(new BugTagModel[mTags.size()]));
        }
    }

    private void bindAssignees(HorizontalHolder holder){
        if (mAssignees.isEmpty()){
            holder.emptycontainer.setVisibility(View.VISIBLE);
        } else {
            new LazyAssigneeLoading(mContext, holder, holder.container).execute(mAssignees.toArray(new UserModel[mAssignees.size()]));
        }
    }

    private void bindComment(CommentHolder holder, BugCommentModel data){
        holder.comment.setText(data.mDescription);
        holder.date.setText(data.mDate);
        holder.username.setText(data.mAuthor.mFirstname + " " + data.mAuthor.mLastname);
    }

    private void bindReply(CommentReplyHolder holder){

    }

    @Override
    public void onBindViewHolder(RecyclerView.ViewHolder holder, int position) {
        switch (getItemViewType(position)){
            case TYPE_TAGS:
                bindTags((HorizontalHolder) holder);
                break;
            case TYPE_ASSIGNEES:
                bindAssignees((HorizontalHolder) holder);
                break;
            case TYPE_SUBTITLE:
                break;
            case TYPE_COMMENT:
                bindComment((CommentHolder) holder, mComments.get(position - 3));
                break;
            case TYPE_COMMENT_REPLY:
                bindReply((CommentReplyHolder) holder);
                break;
            default:
                throw new IllegalArgumentException("Bad viewType : " + getItemViewType(position));
        }
    }

    @Override
    public int getItemCount() {
        return 4 + mComments.size();
    }

    private static class HorizontalHolder extends RecyclerView.ViewHolder{
        public LinearLayout container;
        public TextView emptycontainer;
        public HorizontalHolder(View itemView) {
            super(itemView);
            container = (LinearLayout) itemView.findViewById(R.id.container);
            emptycontainer = (TextView) itemView.findViewById(R.id.emptyview);
        }
    }

    private static class CommentHolder extends RecyclerView.ViewHolder{
        public TextView username, comment, date;
        public ImageButton reply, edit, delete;
        public ImageView avatar;

        public CommentHolder(View itemView) {
            super(itemView);
            username = (TextView) itemView.findViewById(R.id.username);
            comment = (TextView) itemView.findViewById(R.id.comment);
            date = (TextView) itemView.findViewById(R.id.date);
            reply = (ImageButton) itemView.findViewById(R.id.reply);
            edit = (ImageButton) itemView.findViewById(R.id.edit);
            delete = (ImageButton) itemView.findViewById(R.id.delete);
            avatar = (ImageView) itemView.findViewById(R.id.avatar);
        }
    }

    private static class CommentReplyHolder extends RecyclerView.ViewHolder{
        public ImageView avatar;
        public ImageButton send;
        public TextInputEditText comment;

        public CommentReplyHolder(View itemView) {
            super(itemView);
            avatar = (ImageView) itemView.findViewById(R.id.avatar);
            send = (ImageButton) itemView.findViewById(R.id.reply);
            comment = (TextInputEditText) itemView.findViewById(R.id.comment);
        }
    }

    private static class SubtitleHolder extends RecyclerView.ViewHolder{
        public TextView title;

        public SubtitleHolder(View itemView) {
            super(itemView);
            title = (TextView) itemView.findViewById(R.id.title);
        }
    }

    private static class LazyTagLoading extends AsyncTask<BugTagModel, Void, List<Pair<View, BugTagModel>>>{
        private ViewGroup parent;
        private LayoutInflater inflater;
        private HorizontalHolder holder;

        LazyTagLoading(Context context, HorizontalHolder holder, ViewGroup parent){
            inflater = LayoutInflater.from(context);
            this.parent = parent;
            this.holder = holder;
        }

        @Override
        protected List<Pair<View, BugTagModel>> doInBackground(BugTagModel... params) {
            List<Pair<View, BugTagModel>> modelView = new ArrayList<>();
            for (int i = 0; i < params.length; ++i){
                BugTagModel current = params[i];
                View v = inflater.inflate(R.layout.list_item_bugtracker_tagitem, parent, false);
                modelView.add(new Pair<>(v, current));
            }
            return modelView;
        }

        @Override
        protected void onPostExecute(List<Pair<View, BugTagModel>> pairs) {
            for(Pair<View, BugTagModel> viewModel : pairs){
                ((TextView)viewModel.first.findViewById(R.id.tagname)).setText(viewModel.second.name);
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP){
                    viewModel.first.findViewById(R.id.tagname).setBackgroundTintList(ColorStateList.valueOf(Color.parseColor(viewModel.second.color)));
                } else {
                    viewModel.first.findViewById(R.id.tagname).setBackgroundColor(Color.parseColor(viewModel.second.color));
                }
                holder.container.addView(viewModel.first);
            }
            holder.emptycontainer.setVisibility(View.GONE);
        }
    }

    private static class LazyAssigneeLoading extends AsyncTask<UserModel, Void, List<Pair<View, UserModel>>>{

        private ViewGroup parent;
        private LayoutInflater inflater;
        private HorizontalHolder holder;

        LazyAssigneeLoading(Context context, HorizontalHolder holder, ViewGroup parent){
            inflater = LayoutInflater.from(context);
            this.parent = parent;
            this.holder = holder;
        }

        @Override
        protected List<Pair<View, UserModel>> doInBackground(UserModel... params) {
            List<Pair<View, UserModel>> modelView = new ArrayList<>();
            for (UserModel model : params){
                View v = inflater.inflate(R.layout.bug_assignee, parent, false);
                modelView.add(new Pair<>(v, model));
            }
            return modelView;
        }

        @Override
        protected void onPostExecute(List<Pair<View, UserModel>> pairs) {
            for (Pair<View, UserModel> viewModel : pairs){
                ((TextView)viewModel.first.findViewById(R.id.username)).setText(viewModel.second.mFirstname + " " + viewModel.second.mLastname);
                holder.container.addView(viewModel.first);
            }
            holder.emptycontainer.setVisibility(View.GONE);
        }
    }
}
