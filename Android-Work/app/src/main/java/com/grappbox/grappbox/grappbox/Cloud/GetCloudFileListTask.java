package com.grappbox.grappbox.grappbox.Cloud;

import android.app.AlertDialog;
import android.app.Fragment;
import android.content.DialogInterface;
import android.os.AsyncTask;
import android.util.Log;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;
import com.grappbox.grappbox.grappbox.Cloud.CloudExplorerFragment;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.ProtocolException;

/**
 * Created by wieser_m on 17/01/2016.
 */
public class GetCloudFileListTask extends AsyncTask<String, Void, String> {

    private CloudFileAdapter        _adapter;
    private String                  _askedPath;
    private CloudExplorerFragment   _cloudExplorerFragment;

    GetCloudFileListTask(CloudExplorerFragment context, CloudFileAdapter adapter)
    {
        _adapter = adapter;
        _cloudExplorerFragment = context;
    }
    @Override
    protected String doInBackground(String... params) {
        if (params.length < 2)
            return null;
        String token = SessionAdapter.getInstance().getToken();
        String projectId = SessionAdapter.getInstance().getCurrentSelectedProject();
        String path = params[0];
        String passwordSafe = params[1];
        APIConnectAdapter api = APIConnectAdapter.getInstance();


        Log.e("API", path);
        _askedPath = path;
        path = path.replace('/', ',');
        path = path.replace(' ', '|');
        try {
            api.setVersion("V0.2");
            api.startConnection("cloud/list/" + token + "/" + String.valueOf(projectId) + "/" + path + (passwordSafe == "" ? "" : "/" + passwordSafe));
            api.setRequestConnection("GET");

            return api.getInputSream();
        } catch (IOException e) {
            e.printStackTrace();
        }
        return null;
    }

    @Override
    protected void onPostExecute(String s) {
        int responseCode = 500;

        try {
            responseCode = APIConnectAdapter.getInstance().getResponseCode();
        } catch (IOException e) {
            e.printStackTrace();
        }

        if (responseCode < 300) {
            APIConnectAdapter.getInstance().closeConnection();
        }
        JSONObject json = null;
        JSONArray data;

        if (s == null || (_askedPath.startsWith("/Safe") && s.isEmpty()))
        {
            AlertDialog.Builder builder = new AlertDialog.Builder(_adapter.getContext());

            builder.setMessage(R.string.password_error);
            builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    cancel(true);
                }
            });
            builder.create().show();
            if (_cloudExplorerFragment != null) {
                _cloudExplorerFragment.setSafePassword("");
                _cloudExplorerFragment.resetPath();
            }
            return;
        }
        try {
            json = new JSONObject(s);
            if (!json.getJSONObject("info").getString("return_code").startsWith("1."))
            {
                AlertDialog.Builder builder = new AlertDialog.Builder(_adapter.getContext());

                builder.setMessage(_cloudExplorerFragment.getString(R.string.problem_grappbox_server) + _cloudExplorerFragment.getString(R.string.error_code_head) + json.getJSONObject("info").getString("return_message"));
                builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        cancel(true);
                    }
                });
                builder.create().show();
                _cloudExplorerFragment.setSafePassword("");
                _cloudExplorerFragment.resetPath();
                return;
            }
            _adapter.clear();
            if (!_askedPath.equals("/"))
            {
                FileItem item = new FileItem(FileItem.EFileType.BACK, "Go to parent");

                _adapter.add(item);
            }
            data = json.getJSONObject("data").getJSONArray("array");
            for (int i = 0; i < data.length(); ++i)
            {
                FileItem file = new FileItem();

                file.fromJson(data.getJSONObject(i));

                _adapter.add(file);
            }
        } catch (JSONException e) {
            e.printStackTrace();
        }
        super.onPostExecute(s);
    }
}
