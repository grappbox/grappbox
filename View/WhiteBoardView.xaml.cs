using Grappbox.Model;
using Grappbox.ViewModel;
using System;
using System.Diagnostics;
using System.Threading;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Navigation;
using Windows.UI.Xaml;
using System.Collections.Generic;
using Windows.Foundation;
using Windows.UI.Popups;
using Grappbox.Model.Global;
using Grappbox.CustomControls;
using Windows.UI;
using Windows.UI.Xaml.Media;
using Grappbox.Helpers;
using Windows.Networking.PushNotifications;
using Grappbox.HttpRequest;

namespace Grappbox.View
{
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class WhiteBoardView : Page
    {
        private int whiteboardId;

        public WhiteBoardView()
        {
            try
            {
                this.InitializeComponent();
                MainGrid.Width = Window.Current.Bounds.Width;
            }
            catch (Exception e)
            {
                Debug.WriteLine(e.Message);
            }
        }
        protected async override void OnNavigatedTo(NavigationEventArgs e)
        {
            var loader = new LoaderDialog(SystemInformation.GetStaticResource<SolidColorBrush>("GreenGrappboxBrush"));
            loader.ShowAsync();
            whiteboardId = (int)e.Parameter;
            bool result = await viewModel.OpenWhiteboard(whiteboardId);
            if (result == false)
            {
                loader.Hide();
                var dialog = new MessageDialog("Error can't open whiteboard - corrupted data");
                await dialog.ShowAsync();
                if (this.Frame.CanGoBack)
                    this.Frame.GoBack();
                return;
            }
            foreach (WhiteboardObject wo in viewModel.ObjectsList)
            {
                this.drawingCanvas.AddNewElement(wo);
            }
            loader.Hide();
            NotificationManager.NotificationChannel.PushNotificationReceived += OnPushNotification;
        }

        protected override void OnNavigatingFrom(NavigatingCancelEventArgs e)
        {
            base.OnNavigatingFrom(e);
            NotificationManager.NotificationChannel.PushNotificationReceived -= OnPushNotification;
        }

        public void OnPushNotification(PushNotificationChannel sender, PushNotificationReceivedEventArgs e)
        {
            string notificationContent = string.Empty;
            if (e.NotificationType != PushNotificationType.Raw)
                return;
            e.Cancel = true;
            notificationContent = e.RawNotification.Content;
            if (!notificationContent.ToLower().Contains("whiteboard"))
                return;
            try
            {
                viewModel.PullModel = SerializationHelper.DeserializeJson<PullModel>(notificationContent);
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                return;
            }
            runPull();
        }

        public void runPull()
        {
            foreach (WhiteboardObject item in viewModel.PullModel.addObjects)
            {
                this.drawingCanvas.AddNewElement(item);
            }
            foreach (WhiteboardObject item in viewModel.PullModel.delObjects)
            {
                this.drawingCanvas.DeleteElement(item.Id);
            }
            viewModel.LastUpdate = DateTime.Now;
        }
        private async void ToolsButton_Click(object sender, Windows.UI.Xaml.RoutedEventArgs e)
        {
            var tp = new ToolDialog(viewModel.CurrentTool, viewModel.StrokeColor, viewModel.FillColor);
            await tp.ShowAsync();
            viewModel.CurrentTool = tp.SelectedTool;
            Debug.WriteLine(viewModel.CurrentTool);
        }
        private async void FillcolorBtn_Click(object sender, Windows.UI.Xaml.RoutedEventArgs e)
        {
            var colorDialog = new ColorDialog(viewModel.FillColor);
            await colorDialog.ShowAsync();
            viewModel.FillColor = colorDialog.SelectedColor;
        }
        private async void ColorBtn_Click(object sender, Windows.UI.Xaml.RoutedEventArgs e)
        {
            var colorDialog = new ColorDialog(viewModel.StrokeColor);
            await colorDialog.ShowAsync();
            viewModel.StrokeColor = colorDialog.SelectedColor;
            if (viewModel.StrokeColor.Color == Colors.Transparent)
            {
                ColorIndicator.Stroke = new SolidColorBrush(Colors.Black);
                ColorIndicator.StrokeDashArray = new Windows.UI.Xaml.Media.DoubleCollection() { 2 };
            }
            else
            {
                ColorIndicator.Stroke = viewModel.StrokeColor;
                ColorIndicator.StrokeDashArray = new Windows.UI.Xaml.Media.DoubleCollection() { };
            }
        }
        private async void BrushSizeButton_Click(object sender, RoutedEventArgs e)
        {
            var brushDialog = new BrushDialog(this.viewModel.StrokeThickness);
            await brushDialog.ShowAsync();
            viewModel.StrokeThickness = brushDialog.SelectedThickness;
        }

        private async void drawingCanvas_Tapped(object sender, Windows.UI.Xaml.Input.TappedRoutedEventArgs e)
        {
            if (viewModel.CurrentTool == WhiteboardTool.ERAZER)
            {
                int objectId;
                Point p = e.GetPosition(drawingCanvas);
                objectId = await viewModel.deleteObject(new Model.Position() { X = p.X, Y = p.Y });
                if (objectId != -1)
                {
                    if (drawingCanvas.DeleteElement(objectId) == false)
                    {
                        MessageDialog dialogBox = new MessageDialog("Can't delete this object", "Error");
                        await dialogBox.ShowAsync();
                    }
                }
            }
            else if (viewModel.CurrentTool == WhiteboardTool.TEXT)
            {

            }
        }
    }
}