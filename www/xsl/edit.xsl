<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>

<xsl:template match="/page/tree">
    <xsl:param name="rObj" select='"rObj"'/>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="/css/default.css" />
        <link rel="stylesheet" type="text/css" href="/css/jquery-ui.css" />
        <link rel="stylesheet" type="text/css" href="/css/jquery-lightbox.css" />
        <link rel="stylesheet" type="text/css" href="/css/2-column.css" />
        <link rel="stylesheet" type="text/css" href="/css/character-tree.css" />
        
        <script type="text/javascript" src="/js/jquery.js"></script>
        <script type="text/javascript" src="/js/jquery-ui.js"></script>
        <script type="text/javascript" src="/js/jquery-lightbox.js"></script>
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
                vp_cost: "<xsl:value-of select="@vp" />",
                cp_cost: "<xsl:value-of select="@cp" />"
                
			}
			<xsl:if test="position() &lt; last()">,</xsl:if>
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
		    
		    var playerData = {};
		    <xsl:if test="/page/player/@key">
		        var playerData = { member : 'true' };
		    </xsl:if>
		        
		    
            var <xsl:value-of select="$rObj" /> = new ruleManager();
		    
		    $(document).ready(function() {
		        <xsl:value-of select="$rObj" />.ruleInit( 'aspect', ruleData, characterData, playerData );
		        $('#skill_view').tabs({ fxAutoHeight: true, fxFade: true, fxSpeed: 'fast' });
		        $("a#character_sheet").fancybox(); 

		    });
	
	    </script>
	</head>
	<body>
	    <div id="character_sheet" style="display:none">
	    </div>    
        
        
        
         <div class="colmask rightmenu">
            <xsl:call-template name="header">
                <xsl:with-param name="rObj" select="$rObj" />
            </xsl:call-template>                  
                
        	<div class="colleft">
        		<div class="col1">      
                    CP: <span id="cp_unspent"><xsl:value-of select="/page/character/cp/@unspent" /></span> | 
                    VP: <span id="vp_unspent"><xsl:value-of select="/page/character/vp/@unspent" /></span>
            	    <div id="skill_view">    
                            <ul>
                                <li><a href="#fragment-1"><span>Discipline</span></a></li>
                                <li><a href="#fragment-2"><span>Archetype</span></a></li>
                                <li><a href="#fragment-3"><span>Race</span></a></li>
                                <li><a href="#fragment-4"><span>Pool</span></a></li>
				<li><a href="#fragment-5"><span>Vocation</span></a></li>
				<li><a href="#fragment-6"><span>God Powers</span></a></li>
                            </ul>
                            
                        <div id="fragment-1">
                            <xsl:call-template name="showRules">
                                <xsl:with-param name="rules" select="rule[@categoryMask='discipline']" />
                                <xsl:with-param name="rObj" select="$rObj" />
                            </xsl:call-template>          
                        </div>
            
                        
                        <div id="fragment-2">
                            <xsl:call-template name="showRules">
                                <xsl:with-param name="rules" select="rule[@categoryMask='archetype']" />
                                <xsl:with-param name="rObj" select="$rObj" />
                            </xsl:call-template>        
                        </div>
            
                        <div id="fragment-3">
                            <xsl:call-template name="showRules">
                                <xsl:with-param name="rules" select="rule[@categoryMask='race']" />
                                <xsl:with-param name="rObj" select="$rObj" />
                            </xsl:call-template>         
                        </div>
                        
                        
                        <div id="fragment-4">
                            <xsl:call-template name="showRules">
                                <xsl:with-param name="rules" select="rule[@categoryMask='pool']" />
                                <xsl:with-param name="rObj" select="$rObj" />
                            </xsl:call-template>      
                        </div>

                        <div id="fragment-5">
                            <xsl:call-template name="showRules">
                                <xsl:with-param name="rules" select="rule[@categoryMask='vocation']" />
                                <xsl:with-param name="rObj" select="$rObj" />
                            </xsl:call-template>      
                        </div>
                        <div id="fragment-6">
                            <xsl:call-template name="showRules">
                                <xsl:with-param name="rules" select="rule[@categoryMask='godpowers']" />
                                <xsl:with-param name="rObj" select="$rObj" />
                            </xsl:call-template>
                        </div>
                </div> 
                </div>
                
                <div class="col2" id="toolTip"></div>
            </div>

        </div>
    </body>
    </html>
</xsl:template>

<xsl:template name="header">
    <xsl:param name="rObj" />
    <xsl:variable name="cid" select="/page/character/@key" />
    <form name="CharacterEditForm" id="CharacterEditForm" method="post" action="/characters/save">
        <input type="hidden" name="_method" value="POST" />
        <input type="hidden" name="data[Character][id]" value="{$cid}" id="CharacterId" />
        <xsl:for-each select="/page/tree/*">
            <input type="checkbox" name="data[Character][rule][]" id="CharacterRule_{@key}" value="{@key}" style="display:none;" />
       </xsl:for-each> 
        <strong><xsl:value-of select="/page/character/info/@name" /></strong> 
        
        <p>
            <a href="/players/view">Back to player</a> | 
            <a href="/characters/view/{$cid}" id="character_sheet">View Current Character</a>
        </p>
        <span onClick="{$rObj}.send('CharacterEditForm');" id="submitButton"></span>   
    </form>   
</xsl:template>

<xsl:template name="showRules">
    <xsl:param name="rules" />
    <xsl:param name="rObj" />
    <xsl:param name="ruleId" select="@key" />
    
    <xsl:for-each select="$rules">
        <xsl:if test="not(boolean(@essence))">
            <span id="{@key}_rule" style="width:35%;" onmouseover="showTooltip({@key});" onclick="return false;">
            <xsl:attribute name="onmousedown">
                <xsl:value-of select="$rObj" />.selectRule(<xsl:value-of select="@key" />); return false;
            </xsl:attribute>
            
            <xsl:value-of select="@name" />
            </span>

            <span id="{@key}_tooltip" class="tooltip" title="{@name}">
                <p class="tooltip-header"><xsl:value-of select="@name" /> (<xsl:value-of select="@key" />)</p>
                <xsl:if test="@cp &gt; 0">
                    <i>CP:</i> <xsl:value-of select="@cp" /><br />
                </xsl:if>

                <xsl:if test="@vp &gt; 0">
                    <i>VP:</i> <xsl:value-of select="@vp" /><br />
                </xsl:if>
                
                <i>PreReqs:</i><xsl:value-of select="@prereq_desc" /><br />
                
                <p><xsl:value-of select="@description" /></p>
                
                <xsl:if test="boolean(@trained) and  not(boolean(@enabled))">
                    <span class="training-message-fail">Requires Training</span>
                </xsl:if>

                <xsl:if test="boolean(@trained) and boolean(@enabled)">
                    <span class="training-message-success">Training req met</span>
                </xsl:if>

            </span>
        </xsl:if>
    </xsl:for-each>
</xsl:template>
</xsl:stylesheet>