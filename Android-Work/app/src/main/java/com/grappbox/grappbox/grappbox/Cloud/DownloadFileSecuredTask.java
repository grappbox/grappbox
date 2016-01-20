package com.grappbox.grappbox.grappbox.Cloud;

import android.app.Activity;
import android.app.AlertDialog;
import android.app.DownloadManager;
import android.content.Context;
import android.content.DialogInterface;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Environment;
import android.util.Log;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import java.io.IOException;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URI;
import java.net.URISyntaxException;
import java.net.URL;

/**
 * Created by wieser_m on 20/01/2016.
 */
public class DownloadFileSecuredTask extends AsyncTask<String, Void, String> {
    private APIConnectAdapter _api;
    Context _context;
    Activity _activity;

    DownloadFileSecuredTask(Context context, Activity activity)
    {
        _context = context;
        _activity = activity;
    }

    @Override
    protected void onPostExecute(String s) {
        if (s == null)
        {
            AlertDialog.Builder builder = new AlertDialog.Builder(_activity);

            builder.setMessage(R.string.password_error);
            builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    cancel(true);
                }
            });
            builder.show();
        }
        super.onPostExecute(s);
    }

    @Override
    protected String doInBackground(String... params) {
        if (params.length < 4)
            return null;

        String token = SessionAdapter.getInstance().getToken();
        String projectId = String.valueOf(SessionAdapter.getInstance().getCurrentSelectedProject());
        String safePassword = params[2];
        String cloudPath = params[0];
        String password = params[1];
        String filename = params[3];
        cloudPath = cloudPath.replace("/", ",").replace(" ", "|");

        try {
            URL url = new URL("http://api.grappbox.com/app_dev.php/V0.2/cloud/filesecured/" + cloudPath + "/" + token + "/" + projectId + "/" + password + (safePassword.equals("") ? "" : "/" + safePassword));
            Log.e("URL", url.toString());
            HttpURLConnection connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("GET");
            connection.setInstanceFollowRedirects(false);
            connection.getInputStream();
            URL nurl = connection.getURL();
            if (nurl.toString() == url.toString())
                return null;
            DownloadManager.Request request = new DownloadManager.Request(Uri.parse(nurl.toURI().toString()));
            request.allowScanningByMediaScanner();
            request.setTitle(filename);
            request.setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE);
            request.setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, filename);
            DownloadManager manager = (DownloadManager) _context.getSystemService(Context.DOWNLOAD_SERVICE);
            manager.enqueue(request);
        } catch (IOException | URISyntaxException e) {
            e.printStackTrace();
        }

        return "";
    }
}
