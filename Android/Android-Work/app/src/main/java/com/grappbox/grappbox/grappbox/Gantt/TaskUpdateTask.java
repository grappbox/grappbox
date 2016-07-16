package com.grappbox.grappbox.grappbox.Gantt;

import android.content.Context;
import android.os.AsyncTask;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.ProtocolException;
import java.text.ParseException;
import java.util.ArrayList;

/**
 * Created by wieser_m on 14/05/2016.
 */
public class TaskUpdateTask extends AsyncTask<String, Void, String> {
    APIConnectAdapter api;
    Context context;
    ArrayList<TaskDetailActivity.DependencyContainer> dependencies;
    boolean isContainer;
    int advance;
    TaskUpdateListener listener;

    public interface TaskUpdateListener
    {
        public void onUpdateDone(boolean success, Task newTask);
    }

    public TaskUpdateTask(Context context, ArrayList<TaskDetailActivity.DependencyContainer> dependencies, boolean isContainer, int advance, TaskUpdateListener callback) {
        api = APIConnectAdapter.getInstance(true);
        this.context = context;
        this.dependencies = dependencies;
        this.isContainer = isContainer;
        this.advance = advance;
        listener = callback;
    }

    @Override
    protected String doInBackground(String... params) {
        String token = SessionAdapter.getInstance().getToken();
        String taskID = params[0];
        String title = params[1];
        String desc = params[2];
        String color = params[3];
        String due_date = params[4];
        String started_date = params[5];
        JSONObject json = new JSONObject();
        JSONObject data = new JSONObject();
        JSONObject jdue_date = new JSONObject();
        JSONObject jstart_date = new JSONObject();
        JSONArray jdep = new JSONArray();
        try {
            data.put("token", token);
            data.put("taskId", taskID);
            data.put("title", title);
            data.put("description", desc);
            data.put("color", color);
            jdue_date.put("date", due_date);
            jdue_date.put("timezone_type", 3);
            jdue_date.put("timezone","Europe/Paris");
            data.put("due_date", jdue_date);
            data.put("is_container", isContainer);
            for (TaskDetailActivity.DependencyContainer dep : dependencies)
            {
                if (dep.isDeleted)
                    continue;
                boolean error = false;
                JSONObject dependency = new JSONObject();
                switch (dep.linkType)
                {
                    case END_TO_END:
                        dependency.put("name", "ff");
                        break;
                    case END_TO_START:
                        dependency.put("name", "fs");
                        break;
                    case START_TO_END:
                        dependency.put("name", "sf");
                        break;
                    case START_TO_START:
                        dependency.put("name", "ss");
                        break;
                    default:
                        error = true;
                        break;
                }
                dependency.put("id", dep.ID);
                if (error)
                    continue;
                jdep.put(dependency);
            }
            data.put("dependencies", jdep);
            jstart_date.put("date", started_date);
            jstart_date.put("timezone_type", 3);
            jstart_date.put("timezone","Europe/Paris");
            data.put("started_at", jstart_date);
            data.put("advance", advance);
            json.put("data", data);
            api.setVersion("V0.2");
            api.startConnection("tasks/taskupdate");
            api.setRequestConnection("PUT");
            return api.getInputSream();
        } catch (JSONException | IOException e) {
            e.printStackTrace();
        }
        return null;
    }

    @Override
    protected void onPostExecute(String s) {
        super.onPostExecute(s);
        Task taskFetched = null;
        if (s == null || s.isEmpty())
            return;
        try {
            JSONObject json = new JSONObject(s);
            JSONObject info = json.getJSONObject("info");
            if (TaskInfoHandler.process(context, api.getResponseCode(), info))
                return;
            JSONObject data = json.getJSONObject("data");
            taskFetched = new Task(data);
            if (listener != null)
                listener.onUpdateDone(!taskFetched.getId().equals(""), taskFetched);
        } catch (JSONException | IOException | ParseException e) {
            e.printStackTrace();
        }
    }
}
