package com.grappbox.grappbox.grappbox.Cloud;

import android.app.AlertDialog;
import android.app.Fragment;
import android.content.DialogInterface;
import android.os.AsyncTask;
import android.support.design.widget.TabLayout;
import android.util.Log;
import android.view.ViewGroup;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;
import com.grappbox.grappbox.grappbox.Cloud.CloudExplorerFragment;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.ProtocolException;
import java.util.Comparator;

/**
 * Created by wieser_m on 17/01/2016.
 */
public class GetCloudFileListTask extends AsyncTask<String, Void, String> {

    private CloudFileAdapter        _adapter;
    private String                  _askedPath;
    private CloudExplorerFragment   _cloudExplorerFragment;
    private CloudFileListListener   _listener;

    public interface CloudFileListListener
    {
        public void onFetchedSuccess();
    }

    GetCloudFileListTask(CloudExplorerFragment context, CloudFileAdapter adapter)
    {
        _adapter = adapter;
        _cloudExplorerFragment = context;
        _listener = null;
    }

    void SetListener(CloudFileListListener listener)
    {
        _listener = listener;
    }

    @Override
    protected void onPreExecute() {
        super.onPreExecute();
        _cloudExplorerFragment.startLoading(_cloudExplorerFragment.getRootView(), R.id.loader, _cloudExplorerFragment.getRefresher());
        _cloudExplorerFragment.scrollLast();
    }

    @Override
    protected String doInBackground(String... params) {
        if (params.length < 2)
            return null;
        String token = SessionAdapter.getInstance().getToken();
        String projectId = SessionAdapter.getInstance().getCurrentSelectedProject();
        String path = params[0];
        String passwordSafe = params[1];
        APIConnectAdapter api = APIConnectAdapter.getInstance(true);


        Log.e("API", path);
        _askedPath = path;
        path = path.replace('/', ',');
        path = path.replace(' ', '|');
        if (path.isEmpty())
            cancel(true);
        try {
            api.setVersion("V0.2");
            api.startConnection("cloud/list/" + token + "/" + String.valueOf(projectId) + "/" + path + (passwordSafe.isEmpty() ? "" : "/" + passwordSafe));
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
        _adapter.sort(new Comparator<FileItem>() {
            @Override
            public int compare(FileItem lhs, FileItem rhs) {
                int ret = 0;
                if (lhs.get_type() == rhs.get_type())
                {
                    if (lhs.get_filename().equals("Safe"))
                        return -1;
                    else if (rhs.get_filename().equals("Safe"))
                        return 1;
                    return lhs.get_filename().compareTo(rhs.get_filename());
                }

                else if (lhs.get_type() == FileItem.EFileType.DIR && rhs.get_type() == FileItem.EFileType.BACK)
                    return 1;
                else if (lhs.get_type() == FileItem.EFileType.DIR)
                    return -1;
                return 1;
            }
        });
        _cloudExplorerFragment.onRefreshEnd();
        if (_listener != null)
            _listener.onFetchedSuccess();
        if (_askedPath.equals(_cloudExplorerFragment.getPath()))
            _cloudExplorerFragment.endLoading();
        super.onPostExecute(s);
    }
}
