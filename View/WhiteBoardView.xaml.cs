using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Windows.UI.Xaml.Shapes;

// Pour en savoir plus sur le modèle d’élément Page vierge, consultez la page http://go.microsoft.com/fwlink/?LinkID=390556

namespace App1.View
{
    enum shapeType { RECTANGLE, CIRCLE, LINE };
    /// <summary>
    /// Une page vide peut être utilisée seule ou constituer une page de destination au sein d'un frame.
    /// </summary>
    public sealed partial class WhiteBoardView : Page
    {
        Line lineTmp;
        Boolean lineClick = false;
        shapeType g_canvaShapeType = shapeType.RECTANGLE;
        public WhiteBoardView()
        {
            this.InitializeComponent();
            this.drawingCanvas.Tapped += DrawingCanvas_Tapped;
            this.drawingCanvas.PointerMoved += DrawingCanvas_PointerMoved;
        }

        private void DrawingCanvas_PointerMoved(object sender, PointerRoutedEventArgs e)
        {
            if (lineClick == true)
            {
                var pointer = e.GetCurrentPoint(this.drawingCanvas);
                var pos = pointer.Position;
                lineTmp.X2 = pos.X;
                lineTmp.Y2 = pos.Y;
            }
        }

        private void DrawingCanvas_Tapped(object sender, TappedRoutedEventArgs e)
        {
            Point pos = e.GetPosition(this.drawingCanvas);

            switch (this.g_canvaShapeType)
            {
                case shapeType.RECTANGLE:
                    var rect = new Rectangle();
                    rect.Width = 50;
                    rect.Height = 30;
                    rect.Stroke = new SolidColorBrush(Colors.Black);
                    rect.Fill = new SolidColorBrush(Colors.LightGray);
                    this.drawingCanvas.Children.Add(rect);
                    Canvas.SetLeft(rect, pos.X - rect.Width / 2);
                    Canvas.SetTop(rect, pos.Y - rect.Height / 2);
                    break;

                case shapeType.CIRCLE:
                    var circle = new Ellipse();
                    circle.Width = 50;
                    circle.Height = 50;
                    circle.Stroke = new SolidColorBrush(Colors.Black);
                    circle.Fill = new SolidColorBrush(Colors.LightGray);
                    this.drawingCanvas.Children.Add(circle);
                    Canvas.SetLeft(circle, pos.X - circle.Width / 2);
                    Canvas.SetTop(circle, pos.Y - circle.Height / 2);
                    break;

                case shapeType.LINE:
                    if (lineClick == false)
                    {
                        var line = new Line();
                        line.Stroke = new SolidColorBrush(Colors.Black);
                        line.X1 = pos.X;
                        line.Y1 = pos.Y;
                        line.X2 = pos.X;
                        line.Y2 = pos.Y;
                        this.drawingCanvas.Children.Add(line);
                        Canvas.SetLeft(line, 0);
                        Canvas.SetTop(line, 0);
                        this.lineTmp = line;
                        lineClick = true;
                    }
                    else
                    {
                        lineTmp.X2 = pos.X;
                        lineTmp.Y2 = pos.Y;
                        lineClick = false;
                    }
                    break;
            }
            this.drawingCanvas.Background = new SolidColorBrush(Colors.White);
        }


        /// <summary>
        /// Invoqué lorsque cette page est sur le point d'être affichée dans un frame.
        /// </summary>
        /// <param name="e">Données d’événement décrivant la manière dont l’utilisateur a accédé à cette page.
        /// Ce paramètre est généralement utilisé pour configurer la page.</param>
        protected override void OnNavigatedTo(NavigationEventArgs e)
        {

        }

        private void btn_circle_Click(object sender, RoutedEventArgs e)
        {
            this.g_canvaShapeType = shapeType.CIRCLE;
        }

        private void btn_rectangle_Click(object sender, RoutedEventArgs e)
        {
            this.g_canvaShapeType = shapeType.RECTANGLE;
        }

        private void btn_line_Click(object sender, RoutedEventArgs e)
        {
            this.g_canvaShapeType = shapeType.LINE;
        }
    }
}