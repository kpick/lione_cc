<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

<xsl:template match="/page/tree">
    <xsl:param name="rObj" select='"rObj"'/>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="/css/default.css" />
        <link rel="stylesheet" type="text/css" href="/css/3-column.css" />
        <link rel="stylesheet" type="text/css" href="/css/character-tree.css" />
        
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.6/jquery-ui.min.js"></script>
        <script type="text/javascript" src="/js/character-tree.js"></script>
        
	    <!-- JS Data -->
	    <script type="text/javascript">
	        var ruleData = [
	        <xsl:for-each select="/page/tree/*">
            {
                id: "<xsl:value-of select="@key" />",
                prereq:  "<xsl:value-of select="string(@prereq)" />",
                enabled: "<xsl:value-of select="boolean(@enabled)" />",
                locked: "<xsl:value-of select="boolean(@locked)" />",
                is_selected: "<xsl:value-of select="boolean(@selected)" />",
                essence: "<xsl:value-of select="boolean(@essence)" />",
                is_default: "<xsl:value-of select="boolean(@default)" />",
                category: "<xsl:value-of select="@cat_id" />"
			}<xsl:if test="position() &lt; last()">,</xsl:if>
			
		    </xsl:for-each>
		    ];
            
		    var characterData = 
		    { 
		        cp_unspent: <xsl:value-of select="/page/character/cp/@unspent" />,
		        vp_unspent: <xsl:value-of select="/page/character/vp/@unspent" />,
		        lives: <xsl:value-of select="/page/character/bp/@lives" />,
		        godsends: <xsl:value-of select="/page/character/bp/@godsends" />,
		        bp: <xsl:value-of select="/page/character/bp/@points" />
		    };
		    
		    
            var <xsl:value-of select="$rObj" /> = new ruleManager();
		    
		    $(document).ready(function() {
		        <xsl:value-of select="$rObj" />.ruleInit( 'core', ruleData, characterData );
		    });
	
	    </script>
	</head>
	<body>
	    

    <div class="colmask blogstyle">
	<div class="colmid">
		<div class="colleft">
			<div class="col1">
    		         <xsl:call-template name="showCore">
    		            <xsl:with-param name="core" select="rule[@categoryMask2='race']" />
    		            <xsl:with-param name="rObj" select="$rObj" />
    		            <xsl:with-param name="catName" select="'Race'" />
    		         </xsl:call-template>
    		    </div>
    		    
    		    <div class="col2">
                     <xsl:call-template name="showCore">
    		            <xsl:with-param name="core" select="rule[@categoryMask2='kinship']" />
    		            <xsl:with-param name="rObj" select="$rObj" />
    		            <xsl:with-param name="catName" select="'Kinship'" />
    		         </xsl:call-template>
    		    </div>
    		    
    		    <div class="col3">
                     <xsl:call-template name="showCore">
    		            <xsl:with-param name="core" select="rule[@categoryMask2='archetype']" />
    		            <xsl:with-param name="rObj" select="$rObj" />
    		            <xsl:with-param name="catName" select="'Archetype'" />
    		         </xsl:call-template>
    		    </div>
    		 </div>
         </div>

    </div>
    <div id="footer">
        <form name="CharacterAddForm" id="CharacterAddForm" method="post" action="/characters/add/aspects">
            <input type="hidden" name="_method" value="POST" />
            <xsl:for-each select="/page/tree/*">
                 <xsl:if test="boolean(@selected)">
                    <input type="checkbox" name="data[Character][rule][]" id="CharacterRule_{@key}" value="{@key}" style="display:none;" checked="checked" />
                 </xsl:if>
                 <xsl:if test="not(boolean(@selected))">
                    <input type="checkbox" name="data[Character][rule][]" id="CharacterRule_{@key}" value="{@key}" style="display:none;" />
                 </xsl:if>
                 
            </xsl:for-each> 
            <span onClick="{$rObj}.send('CharacterAddForm');" id="submitButton"></span>
            <a href="/players/view" class="cancelButton">Cancel</a>
            
        </form>
        </div>
    </body>
    </html>
</xsl:template>

<xsl:template name="showCore">
    <xsl:param name="core" />
    <xsl:param name="rObj" />
    <xsl:param name="ruleId" select="@key" />
    <xsl:param name="catName" />
    <strong><xsl:value-of select="$catName" /></strong>
    
    <xsl:for-each select="$core">
        <div style="width:100%; height:20px;">
        <xsl:if test="not(boolean(@essence))">
            <span id="{@key}_rule" style="width:100%" onclick="return false;">
            <xsl:attribute name="onmousedown">
                <xsl:value-of select="$rObj" />.selectRule(<xsl:value-of select="@key" />); return false;
            </xsl:attribute>
            <xsl:value-of select="@name" /> 
            </span>


            <span id="{@key}_tooltip" class="tooltip" title="{@name}">
                <p><xsl:value-of select="@name" /></p>
                <xsl:value-of select="@description" />
            </span>
        </xsl:if>
        </div>
    </xsl:for-each>    
    

</xsl:template>
</xsl:stylesheet>